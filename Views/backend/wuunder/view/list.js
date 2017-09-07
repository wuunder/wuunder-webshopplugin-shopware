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
                var data = JSON.parse(record.data.wuunderShipmentData);
                console.log(data);
                if (data !== null) {
                    if (data.id !== "") {
                        return "wuunder-icons wuunder-print-icon";
                    } else {
                        return "wuunder-icons wuunder-create-icon";
                    }
                }
                return "wuunder-icons wuunder-create-icon";
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