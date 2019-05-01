//{block name="backend/order/controller/list"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Wuunder.controller.List', {
    override: 'Shopware.apps.Order.controller.List',

    init: function () {
        var me = this;

        me.control({
            'order-list-main-window order-list': {
                shipOrder: me.onShipOrder,
                resumeShipOrder: me.onResumeShipOrder,
                printLabel: me.onPrintLabel,
                showTrackAndTrace: me.onShowTrackAndTrace
            }
        });

        me.callParent(arguments);
    },

    onShipOrder: function (record) {
        var baseUrl = ("{config name='base_url' namespace='Wuunder'}");
        Ext.Ajax.request({
            method: 'POST',
            url: baseUrl + '/backend/wuunder_shipment/redirect',
            params: { order_id: record.get('id') },
            success: function (response, opts) {
                var data = Ext.decode(response.responseText);
                Ext.util.Cookies.set('wuunderOrderOverviewAfterRedirect', 1);
                console.log(data);
                window.location = data.redirect;
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