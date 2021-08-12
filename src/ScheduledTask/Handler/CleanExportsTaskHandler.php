<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Helper\ExportsCleaner;
use EffectConnect\Marketplaces\ScheduledTask\CleanExportsTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

/**
 * Class CleanExportsTaskHandler
 * @package EffectConnect\Marketplaces\ScheduledTask\Handler
 */
class CleanExportsTaskHandler extends ScheduledTaskHandler
{
    /**
     * @inheritDoc
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [ CleanExportsTask::class ];
    }

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        ExportsCleaner::cleanExports();
    }
}