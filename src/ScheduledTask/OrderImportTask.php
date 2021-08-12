<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask;

/**
 * Class OrderImportTask
 * @package EffectConnect\Marketplaces\ScheduledTask
 */
class OrderImportTask extends AbstractTask
{
    /**
     * @inheritDoc
     */
    public const TASK_NAME          = 'order_import';

    /**
     * @inheritDoc
     */
    public const DEFAULT_INTERVAL   = 900; // 15 minutes.

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