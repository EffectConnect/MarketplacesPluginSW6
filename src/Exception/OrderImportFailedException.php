<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class OrderImportFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $salesChannelId)
 */
class OrderImportFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The order import for sales channel (ID: "%s") failed.';
}