<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Object;

/**
 * Class OrderLineDeliveryData
 * @package EffectConnect\Marketplaces\Object
 */
class OrderLineDeliveryData
{
    /**
     * @var string
     */
    protected $_effectConnectId;

    /**
     * @var string
     */
    protected $_effectConnectLineId;

    /**
     * @var string
     */
    protected $_trackingNumber;

    /**
     * @var string
     */
    protected $_carrier;

    /**
     * OrderLineDeliveryData constructor.
     *
     * @param string $effectConnectLineId
     * @param null|string $effectConnectId
     * @param null|string $trackingNumber
     * @param null|string $carrier
     */
    public function __construct(string $effectConnectLineId, ?string $effectConnectId = null, ?string $trackingNumber = null, ?string $carrier = null)
    {
        $this->_effectConnectLineId = $effectConnectLineId;
        $this->_effectConnectId     = $effectConnectId;
        $this->_trackingNumber      = $trackingNumber;
        $this->_carrier             = $carrier;
    }

    /**
     * @return string
     */
    public function getEffectConnectLineId(): string
    {
        return $this->_effectConnectLineId;
    }

    /**
     * @param string $effectConnectLineId
     * @return OrderLineDeliveryData
     */
    public function setEffectConnectLineId(string $effectConnectLineId): self
    {
        $this->_effectConnectLineId = $effectConnectLineId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEffectConnectId(): string
    {
        return $this->_effectConnectId;
    }

    /**
     * @param string $effectConnectId
     * @return OrderLineDeliveryData
     */
    public function setEffectConnectId(string $effectConnectId): self
    {
        $this->_effectConnectId = $effectConnectId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTrackingNumber(): ?string
    {
        return $this->_trackingNumber;
    }

    /**
     * @param string $trackingNumber
     * @return OrderLineDeliveryData
     */
    public function setTrackingNumber(string $trackingNumber): self
    {
        $this->_trackingNumber = $trackingNumber;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCarrier(): ?string
    {
        return $this->_carrier;
    }

    /**
     * @param string $carrier
     * @return OrderLineDeliveryData
     */
    public function setCarrier(string $carrier): self
    {
        $this->_carrier = $carrier;

        return $this;
    }
}