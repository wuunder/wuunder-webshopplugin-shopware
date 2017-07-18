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
        localStorage.setItem('wuunder_order_id', record.get('id'));
        Shopware.ModuleManager.createSimplifiedModule("WuunderModule", { "title": "Wuunder" });
    }
});
//{/block}