<?php

namespace EffectConnect\Marketplaces\Core\Connection;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ConnectionEntity extends Entity
{
    use EntityIdTrait;

    /** @var string */
    protected $id;

    /** @var string */
    protected $salesChannelId;

    /** @var string|null */
    protected $name;

    /** @var string|null */
    protected $publicKey;

    /** @var string|null */
    protected $secretKey;

    /** @var int */
    protected $catalogExportSchedule;

    /** @var bool|null */
    protected $addLeadingZeroToEan;

    /** @var bool|null */
    protected $useSpecialPrice;

    /** @var bool|null */
    protected $useFallbackTranslations;

    /** @var bool|null */
    protected $useSalesChannelDefaultLanguageAsFirstFallbackLanguage;

    /** @var bool|null */
    protected $useSystemLanguages;

    /** @var int */
    protected $offerExportSchedule;

    /** @var string */
    protected $stockType;

    /** @var int */
    protected $orderImportSchedule;

    /** @var string|null */
    protected $paymentStatus;

    /** @var string|null */
    protected $orderStatus;

    /** @var string|null */
    protected $paymentMethod;

    /** @var string|null */
    protected $shippingMethod;

    /** @var bool|null */
    protected $createCustomer;

    /** @var string|null */
    protected $customerGroup;

    /** @var string|null */
    protected $customerSourceType;

    /** @var bool|null */
    protected $importExternallyFulfilledOrders;

    /** @var string|null */
    protected $externalOrderStatus;

    /** @var string|null */
    protected $externalPaymentStatus;

    /** @var string|null */
    protected $externalShippingStatus;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @return string|null
     */
    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    /**
     * @return int|null
     */
    public function getCatalogExportSchedule(): ?int
    {
        return $this->catalogExportSchedule;
    }

    /**
     * @return bool|null
     */
    public function isAddLeadingZeroToEan(): ?bool
    {
        return $this->addLeadingZeroToEan;
    }

    /**
     * @return bool|null
     */
    public function isUseSpecialPrice(): ?bool
    {
        return $this->useSpecialPrice;
    }

    /**
     * @return bool|null
     */
    public function isUseFallbackTranslations(): ?bool
    {
        return $this->useFallbackTranslations;
    }

    /**
     * @return bool|null
     */
    public function isUseSalesChannelDefaultLanguageAsFirstFallbackLanguage(): ?bool
    {
        return $this->useSalesChannelDefaultLanguageAsFirstFallbackLanguage;
    }

    /**
     * @return bool|null
     */
    public function isUseSystemLanguages(): ?bool
    {
        return $this->useSystemLanguages;
    }

    /**
     * @return int|null
     */
    public function getOfferExportSchedule(): ?int
    {
        return $this->offerExportSchedule;
    }

    /**
     * @return string
     */
    public function getStockType(): ?string
    {
        return $this->stockType;
    }

    /**
     * @return int
     */
    public function getOrderImportSchedule(): ?int
    {
        return $this->orderImportSchedule;
    }

    /**
     * @return string|null
     */
    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    /**
     * @return string|null
     */
    public function getOrderStatus(): ?string
    {
        return $this->orderStatus;
    }

    /**
     * @return string|null
     */
    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @return string|null
     */
    public function getShippingMethod(): ?string
    {
        return $this->shippingMethod;
    }

    /**
     * @return string|null
     */
    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId)
    {
        $this->salesChannelId = $salesChannelId;
    }
}