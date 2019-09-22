<?php
if (!defined('MODX_BASE_PATH')) {
    require 'build.config.php';
}
/** @noinspection PhpIncludeInspection */
require MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');
$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
$modx->loadClass('transport.modPackageBuilder', '', false, true);
if (!XPDO_CLI_MODE) {
    echo '<pre>';
}

$root = dirname(dirname(__FILE__)) . '/';
require_once $root . '_build/includes/functions.php';
$base = $root . 'core/components/pushall/vendor/';
// Clean base dir
if ($dirs = @scandir($base)) {
    foreach ($dirs as $dir) {
        if (!in_array($dir, array('.', '..'))) {
            $path = $base . $dir;
            if (is_dir($path)) {
                removeDir($path);
            }
            else {
                unlink($path);
            }
        }
    }
}

$composerPath = $root . "_build/includes/composer.phar";
@chmod($composerPath, 0777);
$corePath = $root . "core/components/pushall/";
try {
    chdir($corePath);
    $command = "COMPOSER_HOME='{$corePath}' {$composerPath} install  2>&1 ";
    $cmdResult = shell_exec($command);
    $modx->log(modX::LOG_LEVEL_INFO, "Composer:  \n". $cmdResult);
} catch (Exception $e) {
    print_r($e);
}

if (!XPDO_CLI_MODE) {
    echo '</pre>';
}