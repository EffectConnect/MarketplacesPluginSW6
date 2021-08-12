<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class NoPaymentMethodFoundException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct()
 */
class NoPaymentMethodFoundException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'There is no active payment method in the current sales channel.';
}