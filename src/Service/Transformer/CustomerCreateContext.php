<?php

namespace EffectConnect\Marketplaces\Service\Transformer;

use EffectConnect\PHPSdk\Core\Model\Response\BillingAddress;
use EffectConnect\PHPSdk\Core\Model\Response\ShippingAddress;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomerCreateContext
{
    public SalesChannelContext $salesChannelContext;
    public PaymentMethodEntity $paymentMethod;
    public array $billingAddressData;
    public array $shippingAddressData;
    public string $customerGroup;
    /**
     * @var BillingAddress|ShippingAddress
     */
    public $customerSource;
}