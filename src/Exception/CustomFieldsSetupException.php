<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class CustomFieldsSetupException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct()
 */
class CustomFieldsSetupException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Something wen\'t wrong while creating the custom fields setup.';
}