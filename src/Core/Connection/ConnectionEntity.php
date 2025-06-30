<?php

namespace EffectConnect\Marketplaces\Core\Connection;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ConnectionEntity extends Entity
{
    use EntityIdTrait;

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
    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId)
    {
        $this->salesChannelId = $salesChannelId;
    }
    public function getId(): string
    {
        return $this->id;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function getCatalogExportSchedule(): int
    {
        return $this->catalogExportSchedule;
    }

    public function getAddLeadingZeroToEan(): ?bool
    {
        return $this->addLeadingZeroToEan;
    }

    public function getUseSpecialPrice(): ?bool
    {
        return $this->useSpecialPrice;
    }

    public function getUseFallbackTranslations(): ?bool
    {
        return $this->useFallbackTranslations;
    }

    public function getUseSalesChannelDefaultLanguageAsFirstFallbackLanguage(): ?bool
    {
        return $this->useSalesChannelDefaultLanguageAsFirstFallbackLanguage;
    }

    public function getUseSystemLanguages(): ?bool
    {
        return $this->useSystemLanguages;
    }

    public function getOfferExportSchedule(): int
    {
        return $this->offerExportSchedule;
    }

    public function getStockType(): string
    {
        return $this->stockType;
    }

    public function getOrderImportSchedule(): int
    {
        return $this->orderImportSchedule;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function getOrderStatus(): ?string
    {
        return $this->orderStatus;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function getShippingMethod(): ?string
    {
        return $this->shippingMethod;
    }

    public function getCreateCustomer(): ?bool
    {
        return $this->createCustomer;
    }

    public function getCustomerGroup(): ?string
    {
        return $this->customerGroup;
    }

    public function getCustomerSourceType(): ?string
    {
        return $this->customerSourceType;
    }

    public function getImportExternallyFulfilledOrders(): ?bool
    {
        return $this->importExternallyFulfilledOrders;
    }

    public function getExternalOrderStatus(): ?string
    {
        return $this->externalOrderStatus;
    }

    public function getExternalPaymentStatus(): ?string
    {
        return $this->externalPaymentStatus;
    }

    public function getExternalShippingStatus(): ?string
    {
        return $this->externalShippingStatus;
    }
}