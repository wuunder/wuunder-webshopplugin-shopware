//{block name="backend/order/model/order/fields" append}
{
    name: 'wuunderShipmentData', type
:
    'string', useNull
:
    true
}
,
//{/block}

//{block name="backend/order/view/list/list"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Wuunder.view.List', {
    override: 'Shopware.apps.Order.view.list.List',

    createActionColumn: function () {
        var me = this;

        var items = [
            me.createOpenCustomerColumn(),
            /*{if {acl_is_allowed privilege=delete}}*/
            me.createDeleteOrderColumn(),
            /*{/if}*/
            me.createEditOrderColumn()
        ];

        return Ext.create('Ext.grid.column.Action', {
            width: 130,
            items: items.concat(me.createWuunderIcon())
        });
    },

    createWuunderIcon: function () {
        var me = this;

        return [{
            tooltip: 'Ship with Wuunder',
            dataIndex: 'wuunderShipmentData',
            getClass: function (value, meta, record, rowIndex, colIndex, store) {
                var data = JSON.parse(record.data.wuunderShipmentData);
                if (data !== null) {
                    if (data.id !== "" && data.id !== null) {
                        return "wuunder-icons wuunder-hidden-icon";
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

                var data = JSON.parse(record.data.wuunderShipmentData);
                if (data !== null) {
                    if (data.id !== "" && data.id !== null) {
                        me.fireEvent('printLabel', data.labelUrl);
                    } else {
                        me.fireEvent('wuunder-ship-order', record.get('id'));
                    }
                } else {
                    me.fireEvent('wuunder-ship-order', record.get('id'));
                }
            }
        },
            {
                tooltip: 'Print Shipping label',
                dataIndex: 'wuunderShipmentData',
                getClass: function (value, meta, record, rowIndex, colIndex, store) {
                    var data = JSON.parse(record.data.wuunderShipmentData);
                    if (data !== null) {
                        if (data.id !== "" && data.id !== null) {
                            return "wuunder-icons wuunder-print-icon";
                        } else {
                            return "wuunder-icons wuunder-hidden-icon";
                        }
                    }
                    return "wuunder-icons wuunder-hidden-icon";
                },
                /**
                 * Add button handler to fire the showDetail event which is handled
                 * in the list controller.
                 */
                handler: function (view, rowIndex, colIndex, item) {
                    var store = view.getStore(),
                        record = store.getAt(rowIndex);

                    var data = JSON.parse(record.data.wuunderShipmentData);
                    if (data !== null) {
                        if (data.id !== "" && data.id !== null) {
                            me.fireEvent('printLabel', data.labelUrl);
                        } else {
                            me.fireEvent('wuunder-ship-order', record.get('id'));
                        }
                    } else {
                        me.fireEvent('wuunder-ship-order', record.get('id'));
                    }


                }
            },
            {
                tooltip: 'View track and trace info',
                dataIndex: 'wuunderShipmentData',
                getClass: function (value, meta, record, rowIndex, colIndex, store) {
                    var data = JSON.parse(record.data.wuunderShipmentData);
                    if (data !== null) {
                        if (data.id !== "" && data.id !== null) {
                            return "wuunder-icons wuunder-track-icon";
                        } else {
                            return "wuunder-icons wuunder-hidden-icon";
                        }
                    }
                    return "wuunder-icons wuunder-hidden-icon";
                },
                /**
                 * Add button handler to fire the showDetail event which is handled
                 * in the list controller.
                 */
                handler: function (view, rowIndex, colIndex, item) {
                    var store = view.getStore(),
                        record = store.getAt(rowIndex);

                    var data = JSON.parse(record.data.wuunderShipmentData);
                    if (data !== null) {
                        if (data.id !== "" && data.id !== null) {
                            me.fireEvent('showTrackAndTrace', data.trackingAndTraceUrl);
                        }
                    }


                }
            }]
    }
});
//{/block}