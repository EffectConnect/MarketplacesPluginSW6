<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CreateSalutationFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $salutationCode)
 */
class CreateSalutationFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Creating a salutation with salutation code "%s" failed.';
}