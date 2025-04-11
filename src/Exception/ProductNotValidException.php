<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class ProductNotValidException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $id, string $title)
 */
class ProductNotValidException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The product in the orderline has ID "%s", which is not a valid Shopware 6 product ID. This suggests that the product is probably not matched to a Shopware 6-originated product in the EffectConnect catalog (Product title: %s).';
}