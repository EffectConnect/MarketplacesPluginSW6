<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class SalesChannelNotFoundException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $id)
 */
class SalesChannelNotFoundException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'The sales channel with ID "%s" was not found.';
}