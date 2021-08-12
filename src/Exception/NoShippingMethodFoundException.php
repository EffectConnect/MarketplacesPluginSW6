<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class NoShippingMethodFoundException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct()
 */
class NoShippingMethodFoundException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'There is no active shipping method in the current sales channel.';
}