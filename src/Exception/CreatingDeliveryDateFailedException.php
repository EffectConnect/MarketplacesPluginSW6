<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CreatingDeliveryDateFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct()
 */
class CreatingDeliveryDateFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Creating delivery date failed.';
}