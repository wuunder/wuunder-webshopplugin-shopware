<?php

use Shopware\Components\CSRFWhitelistAware;
use Wuunder\Controllers\Traits\ReturnsJson;
use Wuunder\Models\WuunderShipment;

class Shopware_Controllers_Frontend_WuunderShipment extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    use ReturnsJson;

    public function indexAction()
    {
        $order_id = $this->Request()->getParams()['order_id'];
        $params = json_decode(file_get_contents('php://input'), true);
        $entity_manager = $this->container->get('models');

        $shipment_repo = $entity_manager->getRepository(WuunderShipment::class);
        $shipment = $shipment_repo->findOneBy(['order_id' => $order_id]);
        $shipment->setLabelId(json_encode($params));
        $entity_manager->persist($shipment);
        $entity_manager->flush();

        $this->returnJson(['success' => true]);
    }

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