<?php

use Wuunder\Models\WuunderParcelshop;
use Shopware\Models\Order\Order;

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

    protected $config;

    private function getConfig()
    {
        return ($config = Shopware()->Container()
            ->get('shopware.plugin.config_reader')
            ->getByPluginName('Wuunder'));
    }

    public function addressAction()
    {
        if (!empty($this->container->get('session')->get('sUserId'))) {
            $config = $this->getConfig();
            //get user data
            $userData = $this->admin->sGetUserData();
            //Parcelshop locator shipping address and url data
            die(json_encode(array([
                'addressInfo' => urlencode($userData['shippingaddress']['street'] . ' ' . $userData['shippingaddress']['zipcode'] . ' ' . $userData['shippingaddress']['city'] . ' ' . $userData['countryShipping']['iso3']),
                'apiUrl' => intval($config['testmode']) === 1 ? 'https://api-staging.wearewuunder.com/' : 'https://api.wearewuunder.com/',
                'availableCarriers' => $config['available_carriers']
            ])));
        }
    }

    public function parcelshopInfoAction()
    {
        $config = $this->getConfig();
        $parcelshop_id = $this->Request()->getParam('parcelshop_id');
        $apiKey = $config['api_key'];
        if ($this->Request()->getParam('save')) {
            $this->saveParcelshopId($parcelshop_id);
        }
        //Fetch and return Parcelshop address info
        die(json_encode($this->getParcelshopAddress($parcelshop_id, $apiKey)));
    }

    private function saveParcelshopId($id)
    {

        $basket = Shopware()->Session()->connectGetBasket;
        $basketId = $basket['content'][0]['id'];

        $entityManager = $this->getEntityManager();
        $basket_repo = $entityManager->getRepository(\Shopware\Models\Order\Basket::class);
        $basket = $basket_repo->find($basketId);

        $attribute = $basket->getAttribute();
        $attribute->setWuunderconnectorWuunderParcelshopId($id);

        $basket->setAttribute($attribute);
        $entityManager->persist($basket);
        $entityManager->flush();
    }

    function parcelshopCheckAction()
    {
        $basket = Shopware()->Session()->connectGetBasket;
        $basketId = $basket['content'][0]['id'];

        $entityManager = $this->getEntityManager();
        $basket_repo = $entityManager->getRepository(\Shopware\Models\Order\Basket::class);
        $basket = $basket_repo->find($basketId);

        $attribute = $basket->getAttribute();
        $parcelshopId = $attribute->getWuunderconnectorWuunderParcelshopId();
        die(json_encode($parcelshopId));
    }


    function getParcelshopAddress($id, $apiKey)
    {
        $shipping_address = null;
        if (!$id) {
            echo 'empty id???';
        } else {
            $connector = new Wuunder\Connector($apiKey);
            $connector->setLanguage("NL");
            $parcelshopRequest = $connector->getParcelshopById();
            $parcelshopConfig = new \Wuunder\Api\Config\ParcelshopConfig();
            $parcelshopConfig->setId($id);

            if ($parcelshopConfig->validate()) {
                $parcelshopRequest->setConfig($parcelshopConfig);
                if ($parcelshopRequest->fire()) {
                    $parcelshop = $parcelshopRequest->getParcelshopResponse()->getParcelshopData();
                } else {
                    var_dump($parcelshopRequest->getParcelshopResponse()->getError());
                }
            } else {
                $parcelshop = "ParcelshopsConfig not complete";
            }
            return ($parcelshop);
        }
        exit;
    }

    /**
     * returns shopware model manager
     * @return Ambigous <\Shopware\Components\Model\ModelManager, mixed, multitype:>
     */
    public function getEntityManager()
    {
        return Shopware()->Models();
    }
}