<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class InsufficientStockException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $id, string $productNumber, int $availableStock, int $orderedQuantity)
 */
class InsufficientStockException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The product with ID "%s" (SKU: %s) has insufficient stock to fulfill the order (available: %d, ordered: %d).';
}
