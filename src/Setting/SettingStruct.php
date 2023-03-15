<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Setting;

use EffectConnect\Marketplaces\Enum\CustomerSourceType;
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
     * @var string|null
     */
    protected $publicKey;

    /**
     * @var string|null
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
     * @var string
     */
    protected $salesChannelId;

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
     * @var string|null
     */
    protected $paymentMethod;

    /**
     * @var string|null
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
     * @var string|null
     */
    protected $externalShippingStatus;

    /**
     * @var string|null
     */
    protected $externalPaymentStatus;

    /**
     * @var string|null
     */
    protected $externalOrderStatus;

    /**
     * @var string|null
     */
    protected $customerGroup;

    /**
     * @var bool
     */
    protected $createCustomer;

    /**
     * @var string
     */
    protected $customerSourceType;

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
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->getValueWithDefault($this->name, '-');
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return (string)$this->publicKey;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return (string)$this->secretKey;
    }

    /**
     * @return int
     */
    public function getCatalogExportSchedule(): int
    {
        return (int)$this->getValueWithDefault($this->catalogExportSchedule, 43200);
    }

    /**
     * @return bool
     */
    public function isAddLeadingZeroToEan(): bool
    {
        return (bool)$this->getValueWithDefault($this->addLeadingZeroToEan, false);
    }

    /**
     * @return bool
     */
    public function isUseSpecialPrice(): bool
    {
        return (bool)$this->getValueWithDefault($this->useSpecialPrice, true);
    }

    /**
     * @return bool
     */
    public function isUseFallbackTranslations(): bool
    {
        return (bool)$this->getValueWithDefault($this->useFallbackTranslations, true);
    }

    /**
     * @return bool
     */
    public function isUseSalesChannelDefaultLanguageAsFirstFallbackLanguage(): bool
    {
        return (bool)$this->getValueWithDefault($this->useSalesChannelDefaultLanguageAsFirstFallbackLanguage, true);
    }

    /**
     * @return bool
     */
    public function isUseSystemLanguages(): bool
    {
        return (bool)$this->getValueWithDefault($this->useSystemLanguages, false);
    }

    /**
     * @return int
     */
    public function getOfferExportSchedule(): int
    {
        return (int)$this->getValueWithDefault($this->offerExportSchedule, 1800);
    }

    /**
     * @return string
     */
    public function getStockType(): string
    {
        return (string)$this->getValueWithDefault($this->stockType, static::STOCK_TYPE_SALABLE);
    }

    /**
     * @return int
     */
    public function getOrderImportSchedule(): int
    {
        return (int)$this->getValueWithDefault($this->orderImportSchedule, 900);
    }

    /**
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return (string)$this->getValueWithDefault($this->paymentStatus, OrderTransactionStates::STATE_PAID);
    }

    /**
     * @return string
     */
    public function getOrderStatus(): string
    {
        return (string)$this->getValueWithDefault($this->orderStatus, OrderStates::STATE_OPEN);
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return (string)$this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getShippingMethod(): string
    {
        return (string)$this->shippingMethod;
    }

    /**
     * @return ?string
     */
    public function getCustomerGroup(): string
    {
        return (string)$this->customerGroup;
    }

    /**
     * @return bool
     */
    public function isCreateCustomer(): bool
    {
        return $this->getValueWithDefault($this->createCustomer, false);
    }

    /**
     * @return string
     */
    public function getCustomerSourceType(): string
    {
        return (string)$this->getValueWithDefault($this->customerSourceType, CustomerSourceType::BILLING);
    }

    /**
     * @return bool
     */
    public function isImportExternallyFulfilledOrders(): bool
    {
        return $this->getValueWithDefault($this->importExternallyFulfilledOrders, false);
    }

    /**
     * @return string
     */
    public function getExternalShippingStatus(): string
    {
        return (string)$this->externalShippingStatus;
    }

    /**
     * @return string
     */
    public function getExternalPaymentStatus(): string
    {
        return (string)$this->externalPaymentStatus;
    }

    /**
     * @return string
     */
    public function getExternalOrderStatus(): string
    {
        return (string)$this->externalOrderStatus;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }
}