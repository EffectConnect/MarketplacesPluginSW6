<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class ProductNotFoundException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $id, string $title, string $sku)
 */
class ProductNotFoundException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The product with ID "%s" was not found (EAN: %s | SKU: %s).';
}