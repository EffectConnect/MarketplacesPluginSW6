<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CreatingOrderFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $errorMessage)
 */
class CreatingOrderFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Creating order failed (message: "%s").';
}