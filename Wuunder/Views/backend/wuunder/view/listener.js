//{namespace name=backend/index/controller/main}
//{block name="backend/index/controller/main"}
//{$smarty.block.parent}
Ext.define('Shopware.apps.Index.controller.WuunderMain', {
    override: 'Shopware.apps.Index.controller.Main',
    init: function () {
        var me = this;
        me.callParent(arguments);
        var openOrderOverview = Ext.util.Cookies.get('wuunderOrderOverviewAfterRedirect');
        if (openOrderOverview > 0) {
            me.onOpenOrderOverview();
            Ext.util.Cookies.set('wuunderOrderOverviewAfterRedirect', 0);
        }

    },
    onOpenOrderOverview: function () {
        Ext.Function.defer(function () {
            Shopware.app.Application.addSubApplication({
                name: 'Shopware.apps.Order'
            });
        }, 500);
    }
});
//{/block}