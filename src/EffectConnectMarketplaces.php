<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces;

use EffectConnect\Marketplaces\Traits\CustomFieldsTrait;
use EffectConnect\Marketplaces\Traits\PaymentMethodTrait;
use EffectConnect\Marketplaces\Traits\ShippingMethodTrait;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EffectConnectMarketplaces
 * @package EffectConnect\Marketplaces
 */
class EffectConnectMarketplaces extends Plugin
{
    use PaymentMethodTrait, ShippingMethodTrait, CustomFieldsTrait;

    /**
     * @inheritDoc
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * @inheritDoc
     * @param InstallContext $context
     */
    public function install(InstallContext $context) : void
    {
        parent::install($context);

        $this->addPaymentMethod($context->getContext(), $this->container, get_class($this));
        $this->addShipmentMethod($context->getContext(), $this->container);
        $this->addCustomFields($context->getContext(), $this->container);
    }

    /**
     * @inheritDoc
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context): void
    {
        parent::update($context);

        $this->addCustomFields($context->getContext(), $this->container);
    }

    /**
     * @inheritDoc
     * @param InstallContext $context
     */
    public function postInstall(InstallContext $context): void
    {
        parent::postInstall($context);
    }

    /**
     * @inheritDoc
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context) : void
    {
        $this->setPaymentMethodIsActive(false, $context->getContext(), $this->container);
        $this->setShipmentMethodIsActive(false, $context->getContext(), $this->container);

        parent::uninstall($context);
    }

    /**
     * @inheritDoc
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context) : void
    {
        $this->setPaymentMethodIsActive(true, $context->getContext(), $this->container);
        $this->setShipmentMethodIsActive(true, $context->getContext(), $this->container);

        parent::activate($context);
    }

    /**
     * @inheritDoc
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context) : void
    {
        $this->setPaymentMethodIsActive(false, $context->getContext(), $this->container);
        $this->setShipmentMethodIsActive(false, $context->getContext(), $this->container);

        parent::deactivate($context);
    }
}