//{block name="backend/order/controller/list"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Wuunder.controller.List', {
    override: 'Shopware.apps.Order.controller.List',

    init: function () {
        var me = this;

        me.control({
            'order-list-main-window order-list': {
                shipOrder: me.onShipOrder
            }
        });

        me.callParent(arguments);
    },

    onShipOrder: function (record) {
        Ext.Ajax.request({
            method: 'POST',
            url: '/shopware/backend/wuunder_shipment/redirect',
            params: { order_id: record.get('id') },
            success: function (response, opts) {
                var data = Ext.decode(response.responseText);
                window.open(data.redirect, "_blank");
            }
        });
    }
});
//{/block}