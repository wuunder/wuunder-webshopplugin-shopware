//{block name="backend/order/view/list/list"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Wuunder.view.list.List', {
    override: 'Shopware.apps.Order.view.list.List',

    getColumns: function () {
        var me = this;
        var columns = me.callParent(arguments);
        columns.push(me.createWuunderColumn());
        return columns;
    },

    createWuunderIcon: function () {
        var me = this;

        return {
            iconCls: 'sprite-box',
            action: 'shipOrder',
            tooltip: 'Ship with Wuunder',
            /**
             * Add button handler to fire the showDetail event which is handled
             * in the list controller.
             */
            handler: function (view, rowIndex, colIndex, item) {
                var store = view.getStore(),
                    record = store.getAt(rowIndex);

                me.fireEvent('shipOrder', record);
            }
        }
    },

    createWuunderColumn: function () {
        var me = this;

        return Ext.create('Ext.grid.column.Action', {
            width: 50,
            items: [
                me.createWuunderIcon()
            ]
        });
    }
});
//{/block}