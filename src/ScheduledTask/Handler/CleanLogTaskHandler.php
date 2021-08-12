<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Helper\LogCleaner;
use EffectConnect\Marketplaces\ScheduledTask\CleanLogTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

/**
 * Class CleanLogTaskHandler
 * @package EffectConnect\Marketplaces\ScheduledTask\Handler
 */
class CleanLogTaskHandler extends ScheduledTaskHandler
{
    /**
     * @inheritDoc
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [ CleanLogTask::class ];
    }

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        LogCleaner::cleanLog();
    }
}