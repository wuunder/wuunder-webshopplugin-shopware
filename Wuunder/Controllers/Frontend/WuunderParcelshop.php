<?php


class Shopware_Controllers_Frontend_WuunderParcelshop extends Enlight_Controller_Action
{
    /**
     * @var sAdmin
     */
    protected $admin;

    /**
     * Init controller method
     */
    public function init()
    {
        $this->admin = Shopware()->Modules()->Admin();
    }

    public function addressAction()
    {
        if(!empty($this->container->get('session')->get('sUserId'))) {
            //get user data
            $userData = $this->admin->sGetUserData();
            //Parcelshop locator shipping address
            die(json_encode(urlencode($userData['shippingaddress']['street'] . ' ' . $userData['shippingaddress']['zipcode'] . ' ' . $userData['shippingaddress']['city'] . ' ' . $userData['countryShipping']['iso3'])));
        }
    }
}