<?php

namespace Wuunder\Models;

use Doctrine\ORM\Mapping AS ORM;
use Shopware\Components\Model\ModelEntity;
/**
* @ORM\Entity
* @ORM\Table(name="wuunder_parcelshop")
*/
class WuunderParcelshop extends  ModelEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $order_id;

    /**
     * @ORM\Column(name="parcelshop_id", type="text", nullable=true)
     */
    private $parcelshop_id;

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function setParcelshopId($parcelshop_id)
    {
        $this->parcelshop_id = $parcelshop_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParcelshopId()
    {
        return $this->parcelshop_id;
    }


}
