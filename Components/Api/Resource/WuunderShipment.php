<?php

namespace Shopware\Components\Api\Resource;

use Shopware\Components\Api\Exception as ApiException;
use Wuunder\Models\WuunderShipment as ShipmentModel;

class WuunderShipment extends Resource
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(ShipmentModel::class);
    }

    /**
     * @param array $params
     * @return ShipmentModel
     * @throws ApiException\ValidationException
     */
    public function create(array $params)
    {
        $customer_ref = $params['shipment']['customer_reference'];
        $order_id = trim(explode('-', $customer_ref)[0]);
        file_put_contents(__DIR__ . '/resource.txt',
            json_encode($params, JSON_PRETTY_PRINT)."\r\n",
            FILE_APPEND);

        $shipment = new ShipmentModel();
        $shipment->setOrderId($order_id);
        $shipment->setData($params['shipment']);
        $violations = $this->getManager()->validate($shipment);

        if ($violations->count() > 0)
            throw new ApiException\ValidationException($violations);


        $this->getManager()->persist($shipment);
        $this->getManager()->flush();
        return $shipment;
    }
}