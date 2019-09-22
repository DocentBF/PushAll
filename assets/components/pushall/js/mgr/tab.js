Ext.override(MODx.panel.Resource, {

    originals: {
        getFields: MODx.panel.Resource.prototype.getFields
    },

    getFields: function(config) {
        var originals = this.originals.getFields.call(this, config);
        if(typeof(config.record.properties) == 'undefined') {
            config.record.properties = {};
        }
        if(typeof(config.record.properties.pushall) == 'undefined') {
            config.record.properties.pushall = {'pushall_send':0, 'pushall_title': '', 'pushall_text': ''};
        }
        var properties = config.record.properties.pushall;

        for (var i in originals) {
            if (!originals.hasOwnProperty(i)) {
                continue;
            }
            var item = originals[i];

            if (item.id == 'modx-resource-tabs') {
                console.log(properties);
                item.items.push({
                    xtype: "pushall-page",
                    id: "pushall-page",
                    title: _("pushall"),
                    record: {
                        pushall_send: properties['pushall_send'],
                        pushall_title: properties['pushall_title'],
                        pushall_text: properties['pushall_text']
                    }
                });
            }
        }

        return originals;
    }

});
