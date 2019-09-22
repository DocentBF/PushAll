PushAll.panel.Page = function (config) {
    config = config || {};

    Ext.apply(config, {
        border: false,
        id: 'pushall-page',
        baseCls: 'x-panel pushall ' + (MODx.modx23 ? 'modx23' : 'modx22'),
        items: [{
            border: false,
            style: {padding: '5px 15px 15px 15px'},
            layout: 'form',
            labelAlign: 'top',
            items: [{
                xtype: 'checkbox',
                labelSeparator: '',
                hideLabel: true,
                boxLabel: _('pushall_send'),
                name: 'pushall_send',
                checked: Number(config.record.pushall_send) ? true : false,
                inputValue: 1,
            }, {
                xtype: 'textfield',
                fieldLabel: _('pushall_title'),
                name: 'pushall_title',
                anchor: '100%',
                value: config.record.pushall_title,
            }, {
                xtype: 'textarea',
                fieldLabel: _('pushall_text'),
                name: 'pushall_text',
                anchor: '100%',
                height: 300,
                value: config.record.pushall_text,
            },]
        }]
    });
    PushAll.panel.Page.superclass.constructor.call(this, config);
};
Ext.extend(PushAll.panel.Page, MODx.Panel);
Ext.reg('pushall-page', PushAll.panel.Page);
