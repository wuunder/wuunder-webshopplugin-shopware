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
        var me = this;

        Ext.Ajax.request({
            url: '{url action="redirect" controller=wuunder_shipment}',
            params: { order_id: record.get('id') },
            success: function (res) {
                var response = JSON.parse(res.responseText);
                console.log(response);
                var panel = me.createPanel(response.redirect);

                var win = new Ext.Window({
                    title: 'Wuunder shipping',
                    width: 500,
                    height: 500,
                    modal: true,
                    closeAction: 'hide',
                    items: [panel]
                });

                win.show();
            }
        });
    },

    createPanel: function (redirect) {
        //Create panel
        var panel = Ext.create('Shopware.apps.Wuunder.view.Panel');
        var redirect_button = panel.items.items.filter(function (item) {
            return item.cls === 'wuunder-redirect';
        })[0];

        //Set listener on redirect button
        redirect_button.onRedirect = function () {
            console.log(redirect);
            var win = window.open(redirect, "_blank");
        };

        //Return panel
        return panel;
    }
});
//{/block}