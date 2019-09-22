<?php
/** @var modX $modx */
/** @var array $sources */

$settings = array();

$tmp = array(
    'channel_id' => array(
        'xtype' => 'textfield',
        'area' => 'pushall_main',
    ),
    'channel_key' => array(
        'xtype' => 'textfield',
        'area' => 'pushall_main',
    ),
    'templates' => array(
        'xtype' => 'textfield',
        'area' => 'pushall_main',
    ),
    'debug' => array(
        'xtype' => 'combo-boolean',
        'area' => 'pushall_main',
        'value' => '0'
    ),
    'push_type' => array(
        'xtype' => 'textfield',
        'area' => 'pushall_main',
        'value' => 'broadcast',
    ),
    'neworder_send' => array(
        'xtype' => 'combo-boolean',
        'area' => 'pushall_main',
        'value' => '0',
    ),
    

);

foreach ($tmp as $k => $v) {
    /** @var modSystemSetting $setting */
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array_merge(
        array(
            'key' => 'pushall_' . $k,
            'namespace' => PKG_NAME_LOWER,
        ), $v
    ), '', true, true);

    $settings[] = $setting;
}
unset($tmp);

return $settings;
