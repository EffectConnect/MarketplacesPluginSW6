<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Exception;

/**
 * Class ObtainingStateFailedException
 * @package EffectConnect\Marketplaces\Exception
 * @method string __construct(string $type)
 */
class ObtainingStateFailedException extends AbstractException
{
    /**
     * @inheritDoc
     */
    protected const MESSAGE_FORMAT    = 'Obtaining state failed (type: "%s").';
}