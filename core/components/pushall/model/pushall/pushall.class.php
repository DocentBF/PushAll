<?php

use \platx\pushall\PushAll as PushAllAPI;

class PushAll
{
    /** @var modX $modx */
    public $modx;
    private $pa, $error = '';

    const SEND_TIME_LIMIT = 3; // sec

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption('pushall_core_path', $config,
            $this->modx->getOption('core_path') . 'components/pushall/'
        );
        $assetsUrl = $this->modx->getOption('pushall_assets_url', $config,
            $this->modx->getOption('assets_url') . 'components/pushall/'
        );
        $connectorUrl = $assetsUrl . 'connector.php';

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'imagesUrl' => $assetsUrl . 'images/',
            'connectorUrl' => $connectorUrl,

            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'templatesPath' => $corePath . 'elements/templates/',
            'chunkSuffix' => '.chunk.tpl',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'processorsPath' => $corePath . 'processors/',

            'channelID' => (int)trim($this->modx->getOption('pushall_channel_id')),
            'channelKey' => trim($this->modx->getOption('pushall_channel_key')),
            'pushType' => trim($this->modx->getOption('pushall_push_type', null, "broadcast"))
        ), $config);


        $this->modx->addPackage('pushall', $this->config['modelPath']);
        $this->modx->lexicon->load('pushall:default');
        require_once $this->config['corePath'] . 'vendor/autoload.php';
    }

    /**
     * Compares MODX version
     *
     * @param string $version
     * @param string $dir
     *
     * @return bool
     */
    public function systemVersion($version = '2.3.0', $dir = '>=')
    {
        $this->modx->getVersionData();

        return !empty($this->modx->version) && version_compare($this->modx->version['full_version'], $version, $dir);
    }

    /**
     * Loads manager files for tab
     * @param modManagerController $controller
     * @param modResource $resource
     */
    public function loadManagerFiles(modManagerController $controller, modResource $resource, $mode)
    {
        $modx23 = (int)$this->systemVersion();
        $cssUrl = $this->config['cssUrl'] . 'mgr/';
        $jsUrl = $this->config['jsUrl'] . 'mgr/';

        $properties = $resource->get('properties');
        if (!isset($properties['pushall'])) {
            $resource->setProperties(array(
                'pushall_title' => '',
                'pushall_text' => '',
                'pushall_send' => '0'
            ), 'pushall');
            if ($mode != 'new')
                $resource->save();
        }

        $controller->addLexiconTopic('pushall:default');
        $controller->addJavascript($jsUrl . 'pushall.js');
        $controller->addLastJavascript($jsUrl . 'pushall.panel.js');
        $controller->addCss($cssUrl . 'main.css');
        if (!$modx23) {
            $controller->addCss($cssUrl . 'font-awesome.min.css');
        }

        $controller->addHtml("<script type='text/javascript'> 
		    MODx.modx23 = '{$modx23}'; 
		    PushAll.config = '{$this->modx->toJSON($this->config)}'; 
		    PushAll.config.title = '{$properties['pushall_title']}'; 
			PushAll.config.text = '{$properties['pushall_text']}'; 
			PushAll.config.send = '{$properties['pushall_send']}'; 
		</script>");

        $controller->addLastJavascript($jsUrl . 'tab.js');
    }

    /**
     * Используется при сохранении ресурса - экранирование содержимого
     * @param modResource $resource
     */
    public function saveProperties(modResource $resource)
    {
        if ($resource->pushall_send)
            $resource->pushall_send = 0;
        $resource->pushall_title = $this->modx->stripTags($resource->pushall_title);
        $resource->pushall_text = $this->modx->stripTags($resource->pushall_text);

        $props = array(
            'pushall_send' => $resource->pushall_send,
            'pushall_title' => $resource->pushall_title,
            'pushall_text' => $resource->pushall_text,
        );

        $resource->setProperties($props, 'pushall');
    }

    /**
     * @param $pushTitle
     * @param $pushText
     * @param $pushLink
     * @param $type
     * @return array
     * @throws \platx\pushall\exceptions\InvalidIdException
     * @throws \platx\pushall\exceptions\InvalidKeyException
     * @throws \platx\pushall\exceptions\RequiredParameterException
     */

    private function sendNotify($pushTitle, $pushText, $pushLink, $type)
    {
        if (!is_object($this->pa))
            $this->pa = new PushAllAPI($this->config['channelID'], $this->config['channelKey'], PushAllAPI::RESPONSE_TYPE_ARRAY);

        try {
            $r = $this->pa->send(array(
                'type' => $type,
                'title' => $pushTitle,
                'text' => $pushText,
                'url' => $pushLink
            ));
            $response = array(
                'status' => isset($r['status']),
                'message' => $r
            );
        } catch (Exception $e) {
            $response = array(
                'status' => false,
                'message' => $e->getMessage()
            );
        }
        return $response;
    }

    /**
     * Подготавливает отправку пуша
     * @param $pushTitle
     * @param $pushText
     * @param $pushLink
     * @return bool
     */

    public function send($pushTitle, $pushText, $pushLink)
    {
        if (empty($pushTitle))
            $this->error('pushall_err_title_ns', true);
        if (empty($pushText))
            $this->error('pushall_err_text_ns', true);

        if (empty($this->config['channelID']) || empty($this->config['channelKey']))
            $this->error('pushall_err_settings_ns', true);

        if (!empty($this->error)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $this->error);
            return false;
        }

        if (!$this->checkLimit())
            sleep(self::SEND_TIME_LIMIT - round(time() - $_SESSION['pushall']['lastsendtime']));
        try {
            $response = $this->sendNotify($pushTitle, $pushText, $pushLink, $this->config['pushType']);
            $this->modx->log(1, print_r($response, 1));
        } catch (Exception $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($e, 1));
        }

        $isDebug = $this->modx->getOption('pushall_debug');
        if ($isDebug)
            $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($response, 1));

        if (isset($response['message'])) {
            $this->setLastSendTime();
            return true;
        }

        return false;
    }

    /**
     * @param $message
     * @param bool $fromLexicon
     */
    public function error($message, $fromLexicon = false)
    {
        if ($fromLexicon)
            $message = $this->modx->lexicon($message);
        $this->error .= "\n[PushAll] " . $message;
    }

    /**
     * @param $currentTemplate
     * @return bool
     */
    public function isTemplateEnabled($currentTemplate)
    {
        $templates = trim($this->modx->getOption('pushall_templates'));
        if ($templates == '')
            return false;

        if ($templates == 0)
            return true;

        $templates = explode(',', $templates);
        if (is_array($templates)) {
            if (in_array($currentTemplate, $templates))
                return true;
        }

        return false;
    }

    /**
     * Для проверки лимитов
     */
    private function setLastSendTime()
    {
        $_SESSION['pushall']['lastsendtime'] = time();
    }

    /**
     * Проверка лимита отправки по времени
     * @return bool
     */
    private function checkLimit()
    {
        return (time() - $_SESSION['pushall']['lastsendtime'] > self::SEND_TIME_LIMIT);
    }

    /**
     * @param string $chunk
     * @param array $properties
     * @return string
     */
    public function getChunk($chunk, $properties = array())
    {
        if ($pdo = $this->modx->getService('pdoTools')) {
            return $pdo->getChunk($chunk, $properties);
        } else {
            if (preg_match('#^@([A-Z]+)#', $chunk, $matches)) {
                $binding = $matches[1];
                $content = substr($chunk, strlen($binding) + 1);
                $chunk = ltrim($content, ' :');
            }

            $uniqid = uniqid();
            $tmpChunk = $this->modx->newObject('modChunk', array('name' => "{tmp}-{$uniqid}"));
            $tmpChunk->setCacheable(false);
            $tmpChunk->setContent($chunk);
            return $tmpChunk->process($properties, $chunk);
        }
    }

}