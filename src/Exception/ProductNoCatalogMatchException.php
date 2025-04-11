<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class ProductNoCatalogMatchException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $id, string $title)
 */
class ProductNoCatalogMatchException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The product in the orderline has ID "%s", indicating that it is likely not matched to a product in the EffectConnect catalog (Product title: %s).';
}