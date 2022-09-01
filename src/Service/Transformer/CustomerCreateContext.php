<?php

namespace EffectConnect\Marketplaces\Service\Transformer;

use EffectConnect\PHPSdk\Core\Model\Response\BillingAddress;
use EffectConnect\PHPSdk\Core\Model\Response\ShippingAddress;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomerCreateContext
{
    /** @var SalesChannelContext */
    public $salesChannelContext;
    /** @var PaymentMethodEntity */
    public $paymentMethod;
    /** @var array */
    public $billingAddressData;
    /** @var array */
    public $shippingAddressData;
    /** @var string */
    public $customerGroup;
    /** @var BillingAddress|ShippingAddress */
    public $customerSource;
}