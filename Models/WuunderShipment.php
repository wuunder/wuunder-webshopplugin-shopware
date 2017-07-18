<?php

namespace Wuunder\Models;

use Doctrine\ORM\Mapping AS ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="wuunder_shipment")
 */
class WuunderShipment extends ModelEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $order_id;

    /**
     * @ORM\Column(name="data", type="text", nullable=false)
     */
    private $data;

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    public function getData()
    {
        return json_decode($this->data);
    }

    public function setData($data)
    {
        $this->data = json_encode($data);
    }
}