//{block name="backend/order/model/order/fields" append}
{ name: 'wuunderShipmentData', type: 'string', useNull: true },
//{/block}

//{block name="backend/order/view/list/list"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Wuunder.view.List', {
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
            // iconCls: 'wuunder-create-icon',
            action: 'shipOrder',
            tooltip: 'Ship with Wuunder',
            dataIndex:'wuunderShipmentData',
            getClass: function (value, meta, record, rowIndex, colIndex, store) {
                console.log();
                var data =
                // This method can also be used to set the tooltip dynamically
                this.items[0].tooltip = 'Click to open the ' + record.data.toolname + ' window';

                if ()
            },
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
            dataIndex:'wuunderShipmentData',
            items: [
                me.createWuunderIcon()
            ]
        });
    }
});
//{/block}