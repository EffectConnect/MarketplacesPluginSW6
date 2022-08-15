<?php

namespace EffectConnect\Marketplaces\Core\Connection;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ConnectionEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;

    protected $id;
    protected string $salesChannelId;
    protected ?string $name;
    protected ?string $publicKey;
    protected ?string $secretKey;
    protected ?int $catalogExportSchedule;
    protected ?bool $addLeadingZeroToEan;
    protected ?bool $useSpecialPrice;
    protected ?bool $useFallbackTranslations;
    protected ?bool $useSalesChannelDefaultLanguageAsFirstFallbackLanguage;
    protected ?bool $useSystemLanguages;
    protected ?int $offerExportSchedule;
    protected ?string $stockType;
    protected ?int $orderImportSchedule;
    protected ?string $paymentStatus;
    protected ?string $orderStatus;
    protected ?string $paymentMethod;
    protected ?string $shippingMethod;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
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
        return $this->publicKey;
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
        return $this->secretKey;
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
    public function getCatalogExportSchedule(): ?int
    {
        return $this->catalogExportSchedule;
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
        return $this->addLeadingZeroToEan;
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
        return $this->useSpecialPrice;
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
        return $this->useFallbackTranslations;
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
        return $this->useSalesChannelDefaultLanguageAsFirstFallbackLanguage;
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
        return $this->useSystemLanguages;
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
        return $this->offerExportSchedule;
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
        return $this->stockType;
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
        return $this->orderImportSchedule;
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
        return $this->paymentStatus;
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
        return $this->orderStatus;
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
        return $this->paymentMethod;
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
        return $this->shippingMethod;
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
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string $salesChannelId
     * @return ConnectionEntity
     */
    public function setSalesChannelId(string $salesChannelId): self
    {
        $this->salesChannelId = $salesChannelId;

        return $this;
    }

}