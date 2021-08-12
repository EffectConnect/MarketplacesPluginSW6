<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CreateCurlFileException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $reason)
 */
class CreateCurlFileException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Creating the CURL file has failed (reason: %s).';
}