{extends file="parent:frontend/index/index.tpl"}
{block name="frontend_index_header" append}
    <link href="{link file='custom/plugins/Wuunder/Resources/views/frontend/css/parcelshop.css'}" media="all" rel="stylesheet" type="text/css" />
{/block}
{block name='frontend_index_header_javascript_jquery_lib' append}
    <script type="text/javascript">
        var baseUrl = '{config name='base_url' namespace='Wuunder'}';
        var wuunderParcelshopError = '{$wuunderParcelshopError}';
        var parcelshopMethodId = '{config name='parcelshop_method' namespace='Wuunder'}';
    </script>
    <script src="{link file='custom/plugins/Wuunder/Resources/views/frontend/js/parcelshop.js'}" type="text/javascript"></script>
{/block}
