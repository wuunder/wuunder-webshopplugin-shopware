//{block name="backend/order/controller/list"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Wuunder.view.window.Panel', {
    extend: 'Ext.container.Container',
    title: 'Wuunder',
    items: [
        {
            xtype: 'button',
            text: 'Test!',
            cls: '',
            handler: function (args, ar) {

            }
        }
    ]
});
//{/block}