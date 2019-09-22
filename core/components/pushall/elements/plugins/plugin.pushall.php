<?php
$eventName = $modx->event->name;

if(in_array($eventName, array('OnDocFormRender', 'OnBeforeDocFormSave', 'OnDocFormSave'))) {
    if(!isset($resource))
        $resource = $modx->resource;

    if(!($resource instanceof modResource))
        return;

    /** @var modX $modx */
    if (!$PushAll = $modx->getService('pushall', 'PushAll', $modx->getOption('pushall_core_path', null, $modx->getOption('core_path') . 'components/pushall/') . 'model/pushall/', $scriptProperties))
        return;

    if (!$templateEnabled = $PushAll->isTemplateEnabled($resource->get('template')))
        return;

    switch($eventName) {
        case 'OnDocFormRender':
            $PushAll->loadManagerFiles($modx->controller, $resource, $mode);
            $_SESSION['pushall']['send'] = false;
            break;

        case 'OnBeforeDocFormSave':
            $_SESSION['pushall']['send'] = $resource->pushall_send;
            $PushAll->saveProperties($resource);
            break;

        case 'OnDocFormSave':
            $sendEnabled = $_SESSION['pushall']['send'];
            $published = $resource->get('published');

            if($sendEnabled && $published) {
                $pushTitle = !empty($props['pushall_title']) ? $props['pushall_title'] : $resource->pagetitle;
                $pushText = !empty($props['pushall_text']) ? $props['pushall_text'] : $resource->content;
                $pushLink = $modx->makeUrl($resource->id,'','','full');

                $PushAll->send($pushTitle, $pushText, $pushLink);
                $_SESSION['pushall']['send'] = false;
            }

            break;
    }
}

if($eventName == 'msOnCreateOrder') {
    /** @var modX $modx */
    if (!$PushAll = $modx->getService('pushall', 'PushAll', $modx->getOption('pushall_core_path', null, $modx->getOption('core_path') . 'components/pushall/') . 'model/pushall/', $scriptProperties))
        return;

    $newOrderNotify = $modx->getOption('pushall_neworder_send');
    if($newOrderNotify) {
        $modx->lexicon->load('pushall:default');
        $orderData = $msOrder->toArray();
        $pushTitle = $PushAll->getChunk("@INLINE ".$modx->lexicon('pushall_neworder_title'), $orderData);
        $pushText = $PushAll->getChunk("@INLINE ".$modx->lexicon('pushall_neworder_text'), $orderData);
        $pushLink = rtrim($modx->getOption('site_url'), '/') . MODX_MANAGER_URL . "?a=mgr/orders&namespace=minishop2&order=" . $orderData['id'];

        $PushAll->send($pushTitle, $pushText, $pushLink);
    }

}