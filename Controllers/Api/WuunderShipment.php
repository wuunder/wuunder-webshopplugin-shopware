<?php

/**
 * Created by PhpStorm.
 * User: donny
 * Date: 9-6-2017
 * Time: 22:49
 */
class Shopware_Controllers_Api_WuunderShipment extends Shopware_Controllers_Api_Rest
{
    /**
     * @var \Shopware\Components\Api\Resource\Shop $resource
     */
    protected $resource;

    /**
     * @return void
     */
    public function init()
    {
        $this->resource = Shopware\Components\Api\Manager::getResource('WuunderShipment');
    }

    /**
     * @return void
     */
    public function postAction()
    {
        $params = $this->Request()->getPost();
        $this->resource->create($params);
        $this->View()->assign(['success' => true]);
    }
}