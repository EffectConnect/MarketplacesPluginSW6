<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CountryNotFoundException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $countryIsoCode)
 */
class CountryNotFoundException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'There is no country found with ISO-code "%s".';
}