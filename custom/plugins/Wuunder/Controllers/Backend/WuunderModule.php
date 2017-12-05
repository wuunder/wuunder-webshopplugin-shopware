<?php

use Shopware\Components\CSRFWhitelistAware;

class Shopware_Controllers_Backend_WuunderModule extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    public function getWhitelistedCSRFActions()
    {
        return ['index'];
    }

    public function preDispatch()
    {
        $this->get('template')->addTemplateDir(__DIR__ . '/../../Resources/views/');
    }

    public function postDispatch()
    {
        $csrfToken = $this->container->get('BackendSession')->offsetGet('X-CSRF-Token');
        $this->View()->assign(['csrfToken' => $csrfToken]);
    }

    public function indexAction()
    {
    }
}