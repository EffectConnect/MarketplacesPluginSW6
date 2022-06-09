<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Setting;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Class SettingStruct
 * @package EffectConnect\Marketplaces\Setting
 */
class SettingStruct extends Struct
{
    /**
     * Physical Stock Type
     */
    public const STOCK_TYPE_PHYSICAL    = 'physicalStock';

    /**
     * Salable (Available) Stock Type
     */
    public const STOCK_TYPE_SALABLE     = 'salableStock';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var int
     */
    protected $catalogExportSchedule;

    /**
     * @var bool
     */
    protected $addLeadingZeroToEan;

    /**
     * @var bool
     */
    protected $useSpecialPrice;

    /**
     * @var bool
     */
    protected $useFallbackTranslations;

    /**
     * @var bool
     */
    protected $useSalesChannelDefaultLanguageAsFirstFallbackLanguage;

    /**
     * @var bool
     */
    protected $useSystemLanguages;

    /**
     * @var int
     */
    protected $offerExportSchedule;

    /**
     * @var string
     */
    protected $stockType;

    /**
     * @var int
     */
    protected $orderImportSchedule;

    /**
     * @var string
     */
    protected $paymentStatus;

    /**
     * @var string
     */
    protected $orderStatus;

    /**
     * @var string
     */
    protected $paymentMethod;

    /**
     * @var string
     */
    protected $shippingMethod;

    /**
     * @var int
     */
    protected $logExpiration;

    /**
     * @var bool
     */
    protected $importExternallyFulfilledOrders = false;

    /**
     * @var string
     */
    protected $externalShippingStatus;

    /**
     * @var string
     */
    protected $externalPaymentStatus;

    /**
     * @var string
     */
    protected $externalOrderStatus;

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->getValueWithDefault($this->name, '-');
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return (string)$this->publicKey;
    }

    /**
     * @param string $publicKey
     * @return self
     */
    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return (string)$this->secretKey;
    }

    /**
     * @param string $secretKey
     * @return self
     */
    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getCatalogExportSchedule(): int
    {
        return (int)$this->getValueWithDefault($this->catalogExportSchedule, 43200);
    }

    /**
     * @param int $catalogExportSchedule
     * @return self
     */
    public function setCatalogExportSchedule(int $catalogExportSchedule): self
    {
        $this->catalogExportSchedule = $catalogExportSchedule;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAddLeadingZeroToEan(): bool
    {
        return (bool)$this->getValueWithDefault($this->addLeadingZeroToEan, false);
    }

    /**
     * @param bool $addLeadingZeroToEan
     * @return self
     */
    public function setAddLeadingZeroToEan(bool $addLeadingZeroToEan): self
    {
        $this->addLeadingZeroToEan = $addLeadingZeroToEan;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseSpecialPrice(): bool
    {
        return (bool)$this->getValueWithDefault($this->useSpecialPrice, true);
    }

    /**
     * @param bool $useSpecialPrice
     * @return self
     */
    public function setUseSpecialPrice(bool $useSpecialPrice): self
    {
        $this->useSpecialPrice = $useSpecialPrice;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseFallbackTranslations(): bool
    {
        return (bool)$this->getValueWithDefault($this->useFallbackTranslations, true);
    }

    /**
     * @param bool $useFallbackTranslations
     * @return self
     */
    public function setUseFallbackTranslations(bool $useFallbackTranslations): self
    {
        $this->useFallbackTranslations = $useFallbackTranslations;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseSalesChannelDefaultLanguageAsFirstFallbackLanguage(): bool
    {
        return (bool)$this->getValueWithDefault($this->useSalesChannelDefaultLanguageAsFirstFallbackLanguage, true);
    }

    /**
     * @param bool $useSalesChannelDefaultLanguageAsFirstFallbackLanguage
     * @return self
     */
    public function setUseSalesChannelDefaultLanguageAsFirstFallbackLanguage(bool $useSalesChannelDefaultLanguageAsFirstFallbackLanguage): self
    {
        $this->useSalesChannelDefaultLanguageAsFirstFallbackLanguage = $useSalesChannelDefaultLanguageAsFirstFallbackLanguage;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseSystemLanguages(): bool
    {
        return (bool)$this->getValueWithDefault($this->useSystemLanguages, false);
    }

    /**
     * @param bool $useSystemLanguages
     * @return self
     */
    public function setUseSystemLanguages(bool $useSystemLanguages): self
    {
        $this->useSystemLanguages = $useSystemLanguages;

        return $this;
    }

    /**
     * @return int
     */
    public function getOfferExportSchedule(): int
    {
        return (int)$this->getValueWithDefault($this->offerExportSchedule, 1800);
    }

    /**
     * @param int $offerExportSchedule
     * @return self
     */
    public function setOfferExportSchedule(int $offerExportSchedule): self
    {
        $this->offerExportSchedule = $offerExportSchedule;

        return $this;
    }

    /**
     * @return string
     */
    public function getStockType(): string
    {
        return (string)$this->getValueWithDefault($this->stockType, static::STOCK_TYPE_SALABLE);
    }

    /**
     * @param string $stockType
     * @return self
     */
    public function setStockType(string $stockType): self
    {
        $this->stockType = $stockType;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderImportSchedule(): int
    {
        return (int)$this->getValueWithDefault($this->orderImportSchedule, 900);
    }

    /**
     * @param int $orderImportSchedule
     * @return self
     */
    public function setOrderImportSchedule(int $orderImportSchedule): self
    {
        $this->orderImportSchedule = $orderImportSchedule;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return (string)$this->getValueWithDefault($this->paymentStatus, OrderTransactionStates::STATE_PAID);
    }

    /**
     * @param string $paymentStatus
     * @return self
     */
    public function setPaymentStatus(string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderStatus(): string
    {
        return (string)$this->getValueWithDefault($this->orderStatus, OrderStates::STATE_OPEN);
    }

    /**
     * @param string $orderStatus
     * @return self
     */
    public function setOrderStatus(string $orderStatus): self
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return (string)$this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     * @return self
     */
    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getShippingMethod(): string
    {
        return (string)$this->shippingMethod;
    }

    /**
     * @param string $shippingMethod
     * @return self
     */
    public function setShippingMethod(string $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    /**
     * @param $property
     * @param $default
     * @return mixed
     */
    protected function getValueWithDefault($property, $default)
    {
        return !is_null($property) ? $property : $default;
    }

    /**
     * @return bool
     */
    public function isImportExternallyFulfilledOrders(): bool
    {
        return $this->importExternallyFulfilledOrders;
    }

    /**
     * @param bool $importExternallyFulfilledOrders
     * @return SettingStruct
     */
    public function setImportExternallyFulfilledOrders(bool $importExternallyFulfilledOrders): SettingStruct
    {
        $this->importExternallyFulfilledOrders = $importExternallyFulfilledOrders;
        return $this;
    }

    /**
     * @return string
     */
    public function getExternalShippingStatus(): string
    {
        return $this->externalShippingStatus;
    }

    /**
     * @param string $externalShippingStatus
     * @return SettingStruct
     */
    public function setExternalShippingStatus(string $externalShippingStatus): SettingStruct
    {
        $this->externalShippingStatus = $externalShippingStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getExternalPaymentStatus(): string
    {
        return $this->externalPaymentStatus;
    }

    /**
     * @param string $externalPaymentStatus
     * @return SettingStruct
     */
    public function setExternalPaymentStatus(string $externalPaymentStatus): SettingStruct
    {
        $this->externalPaymentStatus = $externalPaymentStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getExternalOrderStatus(): string
    {
        return $this->externalOrderStatus;
    }

    /**
     * @param string $externalOrderStatus
     * @return SettingStruct
     */
    public function setExternalOrderStatus(string $externalOrderStatus): SettingStruct
    {
        $this->externalOrderStatus = $externalOrderStatus;
        return $this;
    }

}