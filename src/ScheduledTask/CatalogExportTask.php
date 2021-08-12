<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask;

/**
 * Class CatalogExportTask
 * @package EffectConnect\Marketplaces\ScheduledTask
 */
class CatalogExportTask extends AbstractTask
{
    /**
     * @inheritDoc
     */
    public const TASK_NAME          = 'catalog_export';

    /**
     * @inheritDoc
     */
    public const DEFAULT_INTERVAL   = 43200; // 12 hours.

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