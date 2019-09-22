var PushAll = function (config) {
    config = config || {};
    PushAll.superclass.constructor.call(this, config);
};
Ext.extend(PushAll, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('pushall', PushAll);

PushAll = new PushAll();