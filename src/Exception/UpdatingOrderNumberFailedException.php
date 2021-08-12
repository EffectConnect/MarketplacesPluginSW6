<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class UpdatingOrderNumberFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $orderId)
 */
class UpdatingOrderNumberFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Updating the order number for order with ID "%s" failed.';
}