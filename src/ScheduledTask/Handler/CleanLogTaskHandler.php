<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use Psr\Log\LoggerInterface;
use EffectConnect\Marketplaces\Helper\LogCleaner;
use EffectConnect\Marketplaces\ScheduledTask\CleanLogTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

/**
 * Class CleanLogTaskHandler
 * @package EffectConnect\Marketplaces\ScheduledTask\Handler
 */
class CleanLogTaskHandler extends ScheduledTaskHandler
{

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger
    )
    {
        parent::__construct($scheduledTaskRepository, $logger);
    }

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