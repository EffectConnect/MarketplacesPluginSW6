<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class NoProductsFoundException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $id)
 */
class NoProductsFoundException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'There are no products found in this sales channel (ID: "%s").';
}