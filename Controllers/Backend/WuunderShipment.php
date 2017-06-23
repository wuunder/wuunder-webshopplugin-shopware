<?php

use Httpful\Request;
use Httpful\Mime;

class Shopware_Controllers_Backend_WuunderShipment extends Shopware_Controllers_Backend_Application
{
    protected $model = 'Shopware\Models\Order\WuunderShipment';
    protected $alias = 'wuunder_shipment';

    private static $CREDENTIALS = 'admin:yMcpdFUIm479Ddldz5K7xHHOXkM16eIKld0s3NuX';
    private static $WEBHOOK_RESOURCE = 'donne1.sition-klanten.nl/api/wuunder_shipment';
    private static $WUUNDER_REDIRECT = 'https://api-staging.wuunder.co/api/bookings?';

    private static $HEADERS = [
        'Accept' => 'application/json+v1',
        'Authorization' => 'Bearer -kF_tkAHRdTjgbZhXYNj7hpBlytcPrdH',
        'Content-Type' => 'application/json',
    ];

    public function __construct(Enlight_Controller_Request_Request $request, Enlight_Controller_Response_Response $response)
    {
        parent::__construct($request, $response);
    }

    public function redirectAction()
    {
        $order_id = $this->Request()->getParam('order_id');

        file_put_contents('req.txt', $this->getWuunderRedirectUrl() . "\r\n", FILE_APPEND);
        $res = Request::post($this->getWuunderRedirectUrl(), $this->getData($order_id))
            ->addHeaders(self::$HEADERS)
            ->sendsAndExpects(Mime::JSON)
            ->send();

        $redirect = $res->headers->toArray()['location'];
        $this->returnJson(['redirect' => $redirect]);
    }

    private function getData($order_id)
    {
        $order_repo = Shopware()->Models()->getRepository(\Shopware\Models\Order\Order::class);

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
            'customer_reference' => $order->getNumber() . ' - ' . $customer->getNumber(),
        ];

        return $body;
    }

    private function getWuunderRedirectUrl()
    {
        $redirect_url = 'http://donne1.sition-klanten.nl/backend';
        $redirect_url = 'redirect_url=' . urlencode($redirect_url);
        $webhook_url = 'http://' . self::$CREDENTIALS . '@' . self::$WEBHOOK_RESOURCE;
        $webhook_url = 'webhook_url=' . urlencode($webhook_url);
        return self::$WUUNDER_REDIRECT . $redirect_url . '&' . $webhook_url;
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

    protected function returnJson($data, $httpCode = 200)
    {
        if ($httpCode !== 200) {
            http_response_code(intval($httpCode));
        }

        header('Content-Type: application/json');

        echo json_encode($data);

        exit;
    }
}