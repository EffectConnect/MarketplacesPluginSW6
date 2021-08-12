<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class OrderUpdateFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $salesChannelId)
 */
class OrderUpdateFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The order update for sales channel (ID: "%s") failed.';
}