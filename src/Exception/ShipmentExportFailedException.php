<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class ShipmentExportFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $salesChannelId)
 */
class ShipmentExportFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The shipment export for sales channel (ID: "%s") failed.';
}