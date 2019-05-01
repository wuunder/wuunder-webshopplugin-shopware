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
     * @ORM\Column(name="order_number", type="integer", nullable=true)
     */
    private $order_number;

    /**
     * @ORM\Column(name="parcelshop_id", type="text", nullable=false)
     */
    private $parcelshop_id;

    /**
     * @ORM\Column(name="user_id", type="text", nullable=true)
     */
    private $user_id;

    public function setOrderNumber($order_number)
    {
        $this->order_number = $order_number;
        return $this;
    }

    public function getOrderNumber()
    {
        return $this->order_number;
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

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return mixed
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }


}
