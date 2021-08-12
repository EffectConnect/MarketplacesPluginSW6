<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class OfferExportFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $salesChannelId)
 */
class OfferExportFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The offer export for sales channel (ID: "%s") failed.';
}