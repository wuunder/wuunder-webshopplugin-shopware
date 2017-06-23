//{block name="backend/order/controller/list"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Wuunder.view.Panel', {
    extend: 'Ext.container.Container',
    title: 'Wuunder',
    items: [
        {
            xtype: 'button',
            text: 'Test!',
            cls: 'wuunder-redirect',
            handler: function (args, ar) {
                this.onRedirect();
            }
        }
    ]
});
//{/block}