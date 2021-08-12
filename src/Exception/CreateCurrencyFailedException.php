<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CreateCurrencyFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $currencyCode)
 */
class CreateCurrencyFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Creating a currency with currency code "%s" failed.';
}