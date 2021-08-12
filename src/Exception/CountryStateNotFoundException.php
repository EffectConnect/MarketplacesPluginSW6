<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CountryStateNotFoundException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $countryStateName)
 */
class CountryStateNotFoundException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'There is no country state found with name "%s".';
}