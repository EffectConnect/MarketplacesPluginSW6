<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class FileCreationException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $location, string $reason)
 */
class FileCreationFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Creating the file for location (%s) has failed (reason: %s).';
}