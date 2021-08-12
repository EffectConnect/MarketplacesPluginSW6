<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Object;

/**
 * Class OrderImportResult
 * @package EffectConnect\Marketplaces\Object
 */
class OrderImportResult
{
    /**
     * @var string
     */
    protected $_effectConnectOrderNumber;

    /**
     * @var string
     */
    protected $_shopwareOrderId;

    /**
     * @var string
     */
    protected $_shopwareOrderNumber;

    /**
     * @var bool
     */
    protected $_importSucceeded;

    /**
     * OrderImportResult constructor.
     *
     * @param string $effectConnectOrderNumber
     * @param string $shopwareOrderId
     * @param string $shopwareOrderNumber
     * @param bool $importSucceeded
     */
    public function __construct(string $effectConnectOrderNumber, bool $importSucceeded, string $shopwareOrderId = '', string $shopwareOrderNumber = '')
    {
        $this->_effectConnectOrderNumber    = $effectConnectOrderNumber;
        $this->_shopwareOrderId             = $shopwareOrderId;
        $this->_shopwareOrderNumber         = $shopwareOrderNumber;
        $this->_importSucceeded             = $importSucceeded;
    }

    /**
     * @return string
     */
    public function getEffectConnectOrderNumber(): string
    {
        return $this->_effectConnectOrderNumber;
    }

    /**
     * @param string $effectConnectOrderNumber
     * @return OrderImportResult
     */
    public function setEffectConnectOrderNumber(string $effectConnectOrderNumber): self
    {
        $this->_effectConnectOrderNumber = $effectConnectOrderNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopwareOrderId(): string
    {
        return $this->_shopwareOrderId;
    }

    /**
     * @param string $shopwareOrderId
     * @return OrderImportResult
     */
    public function setShopwareOrderId(string $shopwareOrderId): self
    {
        $this->_shopwareOrderId = $shopwareOrderId;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopwareOrderNumber(): string
    {
        return $this->_shopwareOrderNumber;
    }

    /**
     * @param string $shopwareOrderNumber
     * @return OrderImportResult
     */
    public function setShopwareOrderNumber(string $shopwareOrderNumber): self
    {
        $this->_shopwareOrderNumber = $shopwareOrderNumber;
        return $this;
    }

    /**
     * @return bool
     */
    public function isImportSucceeded(): bool
    {
        return $this->_importSucceeded;
    }

    /**
     * @param bool $importSucceeded
     * @return OrderImportResult
     */
    public function setImportSucceeded(bool $importSucceeded): self
    {
        $this->_importSucceeded = $importSucceeded;
        return $this;
    }
}