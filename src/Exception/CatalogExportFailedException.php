<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CatalogExportFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $salesChannelId)
 */
class CatalogExportFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The catalog export for sales channel (ID: "%s") failed.';
}