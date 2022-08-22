<?php

namespace EffectConnect\Marketplaces\Core\Connection;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ConnectionEntity extends Entity
{
    use EntityIdTrait;

    protected $id;
    protected $salesChannelId;
    protected $name;
    protected $publicKey;
    protected $secretKey;
    protected $catalogExportSchedule;
    protected $addLeadingZeroToEan;
    protected $useSpecialPrice;
    protected $useFallbackTranslations;
    protected $useSalesChannelDefaultLanguageAsFirstFallbackLanguage;
    protected $useSystemLanguages;
    protected $offerExportSchedule;
    protected $stockType;
    protected $orderImportSchedule;
    protected $paymentStatus;
    protected $orderStatus;
    protected $paymentMethod;
    protected $shippingMethod;

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
     * @return string|null
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @param string|null $publicKey
     * @return self
     */
    public function setPublicKey(?string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    /**
     * @param string|null $secretKey
     * @return self
     */
    public function setSecretKey(?string $secretKey): self
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCatalogExportSchedule(): ?int
    {
        return $this->catalogExportSchedule;
    }

    /**
     * @param int|null $catalogExportSchedule
     * @return self
     */
    public function setCatalogExportSchedule(?int $catalogExportSchedule): self
    {
        $this->catalogExportSchedule = $catalogExportSchedule;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isAddLeadingZeroToEan(): ?bool
    {
        return $this->addLeadingZeroToEan;
    }

    /**
     * @param bool|null $addLeadingZeroToEan
     * @return self
     */
    public function setAddLeadingZeroToEan(?bool $addLeadingZeroToEan): self
    {
        $this->addLeadingZeroToEan = $addLeadingZeroToEan;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isUseSpecialPrice(): ?bool
    {
        return $this->useSpecialPrice;
    }

    /**
     * @param bool|null $useSpecialPrice
     * @return self
     */
    public function setUseSpecialPrice(?bool $useSpecialPrice): self
    {
        $this->useSpecialPrice = $useSpecialPrice;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isUseFallbackTranslations(): ?bool
    {
        return $this->useFallbackTranslations;
    }

    /**
     * @param bool|null $useFallbackTranslations
     * @return self
     */
    public function setUseFallbackTranslations(?bool $useFallbackTranslations): self
    {
        $this->useFallbackTranslations = $useFallbackTranslations;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isUseSalesChannelDefaultLanguageAsFirstFallbackLanguage(): ?bool
    {
        return $this->useSalesChannelDefaultLanguageAsFirstFallbackLanguage;
    }

    /**
     * @param bool|null $useSalesChannelDefaultLanguageAsFirstFallbackLanguage
     * @return self
     */
    public function setUseSalesChannelDefaultLanguageAsFirstFallbackLanguage(?bool $useSalesChannelDefaultLanguageAsFirstFallbackLanguage): self
    {
        $this->useSalesChannelDefaultLanguageAsFirstFallbackLanguage = $useSalesChannelDefaultLanguageAsFirstFallbackLanguage;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isUseSystemLanguages(): ?bool
    {
        return $this->useSystemLanguages;
    }

    /**
     * @param bool|null $useSystemLanguages
     * @return self
     */
    public function setUseSystemLanguages(?bool $useSystemLanguages): self
    {
        $this->useSystemLanguages = $useSystemLanguages;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOfferExportSchedule(): ?int
    {
        return $this->offerExportSchedule;
    }

    /**
     * @param int|null $offerExportSchedule
     * @return self
     */
    public function setOfferExportSchedule(?int $offerExportSchedule): self
    {
        $this->offerExportSchedule = $offerExportSchedule;

        return $this;
    }

    /**
     * @return string
     */
    public function getStockType(): ?string
    {
        return $this->stockType;
    }

    /**
     * @param string|null $stockType
     * @return self
     */
    public function setStockType(?string $stockType): self
    {
        $this->stockType = $stockType;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderImportSchedule(): ?int
    {
        return $this->orderImportSchedule;
    }

    /**
     * @param int|null $orderImportSchedule
     * @return self
     */
    public function setOrderImportSchedule(?int $orderImportSchedule): self
    {
        $this->orderImportSchedule = $orderImportSchedule;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    /**
     * @param string|null $paymentStatus
     * @return self
     */
    public function setPaymentStatus(?string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrderStatus(): ?string
    {
        return $this->orderStatus;
    }

    /**
     * @param string|null $orderStatus
     * @return self
     */
    public function setOrderStatus(?string $orderStatus): self
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @param string|null $paymentMethod
     * @return self
     */
    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getShippingMethod(): ?string
    {
        return $this->shippingMethod;
    }

    /**
     * @param string|null $shippingMethod
     * @return self
     */
    public function setShippingMethod(?string $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string|null $salesChannelId
     * @return ConnectionEntity
     */
    public function setSalesChannelId(?string $salesChannelId): self
    {
        $this->salesChannelId = $salesChannelId;

        return $this;
    }

}