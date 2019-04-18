<?php


namespace Wuunder;


use Enlight_Controller_Response_Response;
use Shopware\Components\CSRFWhitelistAware;

class WuunderParcelshop extends \Enlight_Controller_Action implements CSRFWhitelistAware
{



    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return ['index'];
    }
}
