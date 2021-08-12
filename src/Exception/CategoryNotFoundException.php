<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CategoryNotFoundException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $id)
 */
class CategoryNotFoundException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The category with ID "%s" was not found.';
}