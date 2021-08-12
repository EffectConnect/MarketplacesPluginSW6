<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class InvalidAddressTypeException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct()
 */
class InvalidAddressTypeException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The address should be an instance of EffectConnect\PHPSdk\Core\Model\Response\BillingAddress or EffectConnect\PHPSdk\Core\Model\Response\ShippingAddress.';
}