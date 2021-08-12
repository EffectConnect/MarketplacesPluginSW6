<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CreatingShippingExportRequestFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $message)
 */
class CreatingShippingExportRequestFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Creating the shipping export order update failed (message: "%s").';
}