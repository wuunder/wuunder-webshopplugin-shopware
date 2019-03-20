<?php

class Shopware_Controllers_Frontend_WuunderParcelshop extends \Enlight_Controller_Action
{
    public function preDispatch()
    {
        $pluginPath = $this->container->getParameter('wuunder.plugin_dir');
        
        $this->get('template')->addTemplateDir($pluginPath . '/Resources/views/');
        $this->get('snippets')->addConfigDir($pluginPath . '/Resources/snippets/');
    }

}
