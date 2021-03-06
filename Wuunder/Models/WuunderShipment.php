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
     * @ORM\Column(name="label_id", type="text", nullable=true)
     */
    private $label_id;

    /**
     * @ORM\Column(name="label_url", type="text", nullable=true)
     */
    private $label_url;

    /**
     * @ORM\Column(name="booking_token", type="text", nullable=false)
     */
    private $booking_token;

    /**
     * @ORM\Column(name="booking_url", type="text", nullable=true)
     */
    private $booking_url;

    /**
     * @ORM\Column(name="track_and_trace_url", type="text", nullable=true)
     */
    private $track_and_trace_url;

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * @return mixed
     */
    public function getLabelId()
    {
        return $this->label_id;
    }

    /**
     * @param mixed $label_id
     */
    public function setLabelId($label_id)
    {
        $this->label_id = $label_id;
    }

    /**
     * @return mixed
     */
    public function getLabelUrl()
    {
        return $this->label_url;
    }

    /**
     * @param mixed $label_url
     */
    public function setLabelUrl($label_url)
    {
        $this->label_url = $label_url;
    }

    /**
     * @return mixed
     */
    public function getBookingToken()
    {
        return $this->booking_token;
    }

    /**
     * @param mixed $booking_token
     */
    public function setBookingToken($booking_token)
    {
        $this->booking_token = $booking_token;
    }

    public function getData() {
        return array(
            "id" => $this->label_id,
            "labelUrl" => $this->label_url,
            "token" => $this->booking_token,
            "bookingUrl" => $this->booking_url,
            "trackingAndTraceUrl" =>$this->track_and_trace_url
        );
    }

    /**
     * @return mixed
     */
    public function getBookingUrl()
    {
        return $this->booking_url;
    }

    /**
     * @param mixed $booking_url
     */
    public function setBookingUrl($booking_url)
    {
        $this->booking_url = $booking_url;
    }

    /**
     * @return mixed
     */
    public function getTrackAndTraceUrl()
    {
        return $this->track_and_trace_url;
    }

    /**
     * @param mixed $track_and_trace_url
     */
    public function setTrackAndTraceUrl($track_and_trace_url)
    {
        $this->track_and_trace_url = $track_and_trace_url;
    }
}