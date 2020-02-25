<?php

use Httpful\Request;
use Httpful\Mime;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Detail;
use Wuunder\Models\WuunderShipment;
use Wuunder\Models\WuunderParcelshop;
use Doctrine\ORM\EntityManager;
use Shopware\Components\Model\ModelManager;


class Shopware_Controllers_Backend_WuunderShipment extends Enlight_Controller_Action implements CSRFWhitelistAware
{

    private static $WUUNDER_PLUGIN_VERSION = array("product" => "Shopware extension", "version" => array("build" => "1.3.4", "plugin" => "1.0"));

    public function getWhitelistedCSRFActions()
    {
        return ['redirect', 'getShipments'];
    }

    public function redirectAction()
    {
        $config = Shopware()->Container()
            ->get('shopware.plugin.config_reader')
            ->getByPluginName('Wuunder');


        $api_key = $config['api_key'];
        $explicit_api_key = null;

        if (intval($config['testmode']) === 1) {
            $explicit_api_key = $config['api_key_staging'];
        } else {
            $explicit_api_key = $config['api_key_prod'];
        }

        if (!empty($explicit_api_key)) {
            $api_key = $explicit_api_key;
        }

        $headers = [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ];

        $order_id = $this->Request()->getPost('order_id');

        $url = intval($config['testmode']) === 1 ? 'https://api-staging.wearewuunder.com/api/bookings?' : 'https://api.wearewuunder.com/api/bookings?';
        $data = $this->getData($order_id);

        $res = Request::post($url, $data)
            ->addHeaders($headers)
            ->sendsAndExpects(Mime::JSON)
            ->send();
        $redirect = $res->headers->toArray()['location'];

        $entity_manager = $this->get('models');

        $shipment = new WuunderShipment();
        $shipment->setOrderId($order_id);
        $shipment->setBookingUrl($redirect);
        $shipment->setBookingToken("testtoken");
        $entity_manager->persist($shipment);
        $entity_manager->flush();

        $this->returnJson(['redirect' => $redirect, "error" => $res->body]);
    }

    private function getData($order_id)
    {
        $order_repo = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');

        /** @var Shopware\Models\Order\Order $order */
        $order = $order_repo->find($order_id);
        $shippingAddress = $order->getShipping();

        $customer = $order->getCustomer();
        $address = $customer->getDefaultShippingAddress();

        $description = "";
        $orderDetails = $order->getDetails();
        $value = 0;
        $weight = 0;
        foreach ($orderDetails as $orderDetail) {
            try {
                $description .= '- ' . $orderDetail->getQuantity() . 'x ' . $orderDetail->getArticleName() . "\r\n";
                $value += round($orderDetail->getPrice(), 2) * 100;
                if (is_null($orderDetail->getArticleDetail()) || empty($orderDetail->getArticleDetail())) {
                    $article_repo = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
                    $article = $article_repo->find($orderDetail->getArticleId());
                    $weight += $orderDetail->getQuantity() * round($article->getMainDetail()->getWeight(), 2) * 1000;
                } else {
                    $weight += $orderDetail->getQuantity() * round($orderDetail->getArticleDetail()->getWeight(), 2) * 1000;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        $config = Shopware()->Container()
            ->get('shopware.plugin.config_reader')
            ->getByPluginName('Wuunder');

        $selectedDispatch = $order->getDispatch()->getId();

        $preferredServiceLevel = "";
        for ($i = 1; $i < 5; $i++) {
            if ($config['mapping_' . $i . '_method'] == $selectedDispatch) {
                $preferredServiceLevel = $config['mapping_' . $i . '_filter'];
                break;
            }
        }

        $order_detail_repo = Shopware()->Models()->getRepository('Shopware\Models\Order\Detail');
        $detail = $order_detail_repo->findOneBy(['orderId' => $order_id]);
        if (!empty($detail->getAttribute())) {
            $parcelshopId = $detail->getAttribute()->getWuunderconnectorWuunderParcelshopId();
        }

        $delivery_address = [
            'business' => $shippingAddress->getCompany(),
            'chamber_of_commerce_number' => $address->getVatId(),
            'country' => $shippingAddress->getCountry()->getIso(),
            'email_address' => $customer->getEmail(),
            'family_name' => $shippingAddress->getLastname(),
            'given_name' => $shippingAddress->getFirstname(),
            'locality' => $shippingAddress->getCity(),
            'phone_number' => $address->getPhone(),
            'street_name' => $shippingAddress->getStreet(),
            'zip_code' => $shippingAddress->getZipcode(),
        ];

        $config = Shopware()->Container()
            ->get('shopware.plugin.config_reader')
            ->getByPluginName('Wuunder');
        $base_url = $config['base_url'];
        $redirect_url = $base_url . '/backend/';
        $webhook_url = $base_url . '/wuunder_shipment?order_id=' . $order_id;

        $body = [
            'pickup_address' => $this->getPickupAddress(),
            'delivery_address' => $delivery_address,
            'customer_reference' => $order->getNumber(),
            'description' => $description,
            'value' => round($value),
            'weight' => $weight,
            'preferred_service_level' => $preferredServiceLevel,
            'source' => self::$WUUNDER_PLUGIN_VERSION,
            'parcelshop_id' => isset($parcelshopId) ? $parcelshopId : null,
            'redirect_url' => $redirect_url,
            'webhook_url' => $webhook_url
        ];

        return $body;
    }

    private function getPickupAddress()
    {
        $config = Shopware()->Container()
            ->get('shopware.plugin.config_reader')
            ->getByPluginName('Wuunder');

        //Adding house_number and phone_number causes problems for some reason
        return [
            'business' => $config['business'],
            'chamber_of_commerce_number' => $config['coc_number'],
            'country' => $config['country'],
            'email_address' => $config['email'],
            'family_name' => $config['lastname'],
            'given_name' => $config['firstname'],
            'house_number' => $config['house_number'],
            'locality' => $config['locality'],
            'phone_number' => $config['phone_number'],
            'street_name' => $config['street_name'],
            'zip_code' => $config['zip_code'],
        ];
    }

    private function getShop()
    {
        $em = $this->get('models');
        $repo = $em->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repo->findById(1);
        return $shop[0];
    }

    /**
     * Return JSON
     *
     * @param $data
     * @param int $httpCode
     */
    protected function returnJson($data, $httpCode = 200)
    {
        if ($httpCode !== 200)
            http_response_code(intval($httpCode));
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}