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
     * @var string
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
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
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
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getShippingMethod(): string
    {
        return $this->shippingMethod;
    }

    /**
     * @return ?string
     */
    public function getCustomerGroup(): string
    {
        return $this->customerGroup;
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
        return $this->externalShippingStatus;
    }

    /**
     * @return string
     */
    public function getExternalPaymentStatus(): string
    {
        return $this->externalPaymentStatus;
    }

    /**
     * @return string
     */
    public function getExternalOrderStatus(): string
    {
        return $this->externalOrderStatus;
    }

    public function getValues(): array
    {
        $methods = get_class_methods($this);



    }
}