<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask;

/**
 * Class OfferQueueTask
 * @package EffectConnect\Marketplaces\ScheduledTask
 */
class OfferQueueTask extends AbstractTask
{
    /**
     * @inheritDoc 
     */
    public const TASK_NAME          = 'offer_queue_task';

    /**
     * @inheritDoc
     */
    public const DEFAULT_INTERVAL   = 60;

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