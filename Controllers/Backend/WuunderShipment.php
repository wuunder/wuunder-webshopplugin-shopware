<?php

use Httpful\Request;
use Httpful\Mime;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Order\Order;
use Wuunder\Controllers\Traits\ReturnsJson;
use Wuunder\Models\WuunderShipment;

class Shopware_Controllers_Backend_WuunderShipment extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    use ReturnsJson;

    private static $WUUNDER_REDIRECT = 'https://api-staging.wuunder.co/api/bookings?';

    private static $HEADERS = [
        'Accept' => 'application/json+v1',
        'Authorization' => 'Bearer YVc7rKdM6e6Q_HQK81NCt7SM0LT0TtQB',
        'Content-Type' => 'application/json',
    ];

    public function getWhitelistedCSRFActions()
    {
        return ['redirect', 'getShipments'];
    }

    public function redirectAction()
    {
        $order_id = $this->Request()->getPost('order_id');

        $url = $this->getWuunderRedirectUrl($order_id);
        $data = $this->getData($order_id);

        $res = Request::post($url, $data)
            ->addHeaders(self::$HEADERS)
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

        $this->returnJson(['redirect' => $redirect]);
    }

    private function getWuunderRedirectUrl($order_id)
    {
        $shop = $this->getShop();

        $redirect_url = 'http://' . $shop->getHost() . '/backend';
        $redirect_url = 'redirect_url=' . urlencode($redirect_url);
        $webhook_url = 'http://' . $shop->getHost() . '/wuunder_shipment?order_id=' . $order_id;
        $webhook_url = 'webhook_url=' . urlencode($webhook_url);

        return self::$WUUNDER_REDIRECT . $redirect_url . '&' . $webhook_url;
    }

    private function getData($order_id)
    {
        $order_repo = Shopware()->Models()->getRepository(Order::class);

        /** @var Shopware\Models\Order\Order $order */
        $order = $order_repo->find($order_id);
        $customer = $order->getCustomer();
        $address = $customer->getDefaultShippingAddress();
        $address_parts = explode(' ', $address->getStreet());
        $street_name = trim($address_parts[0]);
        $house_number = trim($address_parts[1]);

        $delivery_address = [
            'business' => $address->getCompany(),
            'chamber_of_commerce_number' => $address->getVatId(),
            'country' => $address->getCountry()->getIso(),
            'email_address' => $customer->getEmail(),
            'family_name' => $customer->getLastname(),
            'given_name' => $customer->getFirstname(),
            'house_number' => $house_number,
            'locality' => $address->getCity(),
            'phone_number' => $address->getPhone(),
            'street_name' => $street_name,
            'zip_code' => $address->getZipcode(),
        ];

        $body = [
            'pickup_address' => $this->getPickupAddress(),
            'delivery_address' => $delivery_address,
            'customer_reference' => $customer->getNumber(),
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
//            'house_number' => $config['house_number'],
            'locality' => $config['locality'],
//            'phone_number' => $config['phone_number'],
            'street_name' => $config['street_name'],
            'zip_code' => $config['zip_code'],
        ];
    }

    private function getShop()
    {
        $em = $this->get('models');
        $repo = $em->getRepository(Shopware\Models\Shop\Shop::class);
        $shop = $repo->findById(1);
        return $shop[0];
    }
}