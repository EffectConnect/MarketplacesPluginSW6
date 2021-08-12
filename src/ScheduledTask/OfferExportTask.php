<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask;

/**
 * Class OfferExportTask
 * @package EffectConnect\Marketplaces\ScheduledTask
 */
class OfferExportTask extends AbstractTask
{
    /**
     * @inheritDoc
     */
    public const TASK_NAME          = 'offer_export';

    /**
     * @inheritDoc
     */
    public const DEFAULT_INTERVAL   = 1800; // 30 minutes.

    /**
     * Get the task name.
     *
     * @return string
     */
    public static function getTaskName(): string
    {
        return static::VENDOR_PREFIX . '.' . static::TASK_NAME;
    }

    /**
     * Get the default interval.
     *
     * @return int
     */
    public static function getDefaultInterval(): int
    {
        return static::DEFAULT_INTERVAL;
    }
}