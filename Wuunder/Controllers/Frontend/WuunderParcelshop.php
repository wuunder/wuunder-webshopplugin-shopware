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
        return($config = Shopware()->Container()
            ->get('shopware.plugin.config_reader')
            ->getByPluginName('Wuunder'));
    }

    public function addressAction()
    {
        if(!empty($this->container->get('session')->get('sUserId'))) {
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

    private function saveParcelshopId($id) {
        $entityManager = $this->getEntityManager();
        $userId = $this->container->get('session')->get('sUserId');
        $db = $this->container->get('dbal_connection');
        $sql = 'SELECT * FROM wuunder_parcelshop WHERE user_id = ' . $userId. ' ORDER BY id DESC LIMIT 1';
        $parcelshopData = $db->fetchAll($sql);
        //if user has selected a parcelshop before, overwrite it in the DB, else save new parcelshop

        if($parcelshopData && $parcelshopData[0]['order_id'] === NULL) {
            $sql = 'UPDATE wuunder_parcelshop SET parcelshop_id = "' . $id . '" WHERE user_id = ' . $userId . ' AND  order_id IS NULL ORDER BY id DESC LIMIT 1';
            Shopware()->Db()->executeQuery($sql);
        } else {
            $parcelshop = new WuunderParcelshop();
            $parcelshop->setParcelshopId($id);
            $parcelshop->setUserId($userId);
            $entityManager->persist($parcelshop);
            $entityManager->flush();
        }
    }

    function parcelshopCheckAction() {
        $userId = $this->container->get('session')->get('sUserId');
        $sql = 'SELECT * FROM wuunder_parcelshop WHERE user_id = ' . $userId. ' AND order_id IS NULL ORDER BY id DESC LIMIT 1';
        $db = $this->container->get('dbal_connection');
        $parcelshopData = $db->fetchAll($sql);
        die(json_encode($parcelshopData[0]['parcelshop_id']));
    }


    function getParcelshopAddress($id, $apiKey) {
        $shipping_address = null;
        if(!$id) {
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
           return($parcelshop);
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