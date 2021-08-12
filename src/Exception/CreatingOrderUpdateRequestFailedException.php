<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CreatingOrderUpdateRequestFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $effectConnectOrderNumber, string $message)
 */
class CreatingOrderUpdateRequestFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Creating the order update failed (EffectConnect order number: "%s" | message: "%s").';
}