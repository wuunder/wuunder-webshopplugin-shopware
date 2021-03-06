<?php

use Shopware\Components\CSRFWhitelistAware;
use Wuunder\Controllers\Traits\ReturnsJson;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use Wuunder\Models\WuunderShipment;

class Shopware_Controllers_Frontend_WuunderShipment extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    use ReturnsJson;

    public function indexAction()
    {
        $order_id = $this->Request()->getParams()['order_id'];
        $params = json_decode(file_get_contents('php://input'), true);
        $entity_manager = $this->container->get('models');

        $shipment_repo = $entity_manager->getRepository('Wuunder\Models\WuunderShipment');
        $shipment = $shipment_repo->findOneBy(['order_id' => $order_id]);
        $data = $params['shipment'];

        if ($params['action'] === "shipment_booked") {
            //$tt = $data['track_and_trace_url'];
            $shipment->setTrackAndTraceUrl($data['track_and_trace_url']);
            $shipment->setLabelId($data['id']);
            $shipment->setLabelUrl($data['label_url']);

            $order_repo = $entity_manager->getRepository('Shopware\Models\Order\Order');
            $order = $order_repo->find($order_id);
            $config = $this->container
                ->get('shopware.plugin.config_reader')
                ->getByPluginName('Wuunder');
            $orderState = $entity_manager->getRepository('Shopware\Models\Order\Status')->find((int)$config['order_status']);
            $order->setOrderStatus($orderState);

            $orderDetails = $order->getDetails();

            foreach ($orderDetails as $detail) {
                $detail->setShipped(true);
            }

            $entity_manager->persist($shipment);
            $entity_manager->flush();

            $this->returnJson(['success' => true]);
        }
        if ($params['action'] === "track_and_trace_updated") {
            $order_repo = $entity_manager->getRepository('Shopware\Models\Order\Order');
            $order = $order_repo->find($order_id);
            $order->setTrackingCode($params['track_and_trace_code']);
            $entity_manager->persist($shipment);
            $entity_manager->flush();
        }
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
