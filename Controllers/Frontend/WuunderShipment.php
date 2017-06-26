<?php

use Shopware\Components\CSRFWhitelistAware;
use Wuunder\Models\WuunderShipment;

class Shopware_Controllers_Frontend_WuunderShipment extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    public function indexAction()
    {
        $params = json_decode(file_get_contents('php://input'), true);
        $customer_ref = $params['shipment']['customer_reference'];
        $order_id = trim(explode('-', $customer_ref)[0]);

        file_put_contents(__DIR__ . '/params.txt',
            json_encode($params, JSON_PRETTY_PRINT) . "\r\n",
            FILE_APPEND);

        $entity_manager = $this->container->get('models');
        $shipment = new WuunderShipment();
        $shipment->setOrderId($order_id);
        $shipment->setData($params['shipment']);
        $entity_manager->persist($shipment);
        $entity_manager->flush();

        $this->returnJson(['success' => true]);
    }

    protected function returnJson($data, $httpCode = 200)
    {
        if ($httpCode !== 200) {
            http_response_code(intval($httpCode));
        }

        header('Content-Type: application/json');

        echo json_encode($data);

        exit;
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