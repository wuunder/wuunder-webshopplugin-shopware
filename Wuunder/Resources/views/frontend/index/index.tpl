{extends file="parent:frontend/index/index.tpl"}
{block name="document_index_css"}
    <style {link file='custom/plugins/Wuunder/Resources/views/frontend/css/parcelshop.css'}></style>
{/block}
{block name='frontend_index_header_javascript_jquery_lib' append}
    <script type="text/javascript">
        var baseUrl = '{config name='base_url' namespace='Wuunder'}';
    </script>
    <script src="{link file='custom/plugins/Wuunder/Resources/views/frontend/js/parcelshop.js'}" type="text/javascript"></script>
{/block}
