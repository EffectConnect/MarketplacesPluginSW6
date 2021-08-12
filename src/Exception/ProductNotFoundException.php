<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class ProductNotFoundException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $id, string $ean, string $sku)
 */
class ProductNotFoundException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Product with ID "%s" is not found (EAN: %s | SKU: %s).';
}