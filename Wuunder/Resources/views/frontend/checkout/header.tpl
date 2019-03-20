   {* Include own Javascript Code *}
   {extends file='header.tpl'}
    {block name='frontend_index_top_bar_container'}{/block}
        {debug}
        {if $myVariable}<script type="text/javascript" src="{link   file='frontend/_public/src/js/myFile.js'}"></script>{/if}
    {/block}