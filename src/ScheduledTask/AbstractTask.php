<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * Class AbstractTask
 * @package EffectConnect\Marketplaces\ScheduledTask
 */
abstract class AbstractTask extends ScheduledTask
{
    /**
     * The vendor prefix for the task.
     */
    public const VENDOR_PREFIX      = 'effectconnect_marketplaces';

    /**
     * The name for the task.
     */
    public const TASK_NAME          = 'task';

    /**
     * The default interval for the task.
     */
    public const DEFAULT_INTERVAL   = 3600; // 60 minutes.

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