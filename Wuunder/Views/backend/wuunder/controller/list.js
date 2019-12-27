//{block name="backend/order/controller/list"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Wuunder.controller.List', {
    override: 'Shopware.apps.Order.controller.List',

    init: function () {
        var me = this;

        me.callParent(arguments);

        me.control({
            'order-list-main-window order-list': {
                shipOrder: me.onShipOrder,
                resumeShipOrder: me.onResumeShipOrder,
                printLabel: me.onPrintLabel,
                showTrackAndTrace: me.onShowTrackAndTrace
            }
        });
    },

    onShipOrder: function (record) {
        Ext.Ajax.request({
            method: 'POST',
            url: '{url controller=WuunderShipment action=redirect}',
            params: { order_id: record.get('id') },
            success: function (response, opts) {
                var data = Ext.decode(response.responseText);
                Ext.util.Cookies.set('wuunderOrderOverviewAfterRedirect', 1);
                window.location.href = data.redirect;
            }
        });
    },
    onResumeShipOrder: function (url) {
        window.location = url;
    },
    onPrintLabel: function (url) {
        window.open(url);
    },
    onShowTrackAndTrace: function (url) {
        window.open(url);
    }
});
//{/block}