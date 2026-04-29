<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use Psr\Log\LoggerInterface;
use EffectConnect\Marketplaces\Helper\ExportsCleaner;
use EffectConnect\Marketplaces\ScheduledTask\CleanExportsTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

/**
 * Class CleanExportsTaskHandler
 * @package EffectConnect\Marketplaces\ScheduledTask\Handler
 */
class CleanExportsTaskHandler extends ScheduledTaskHandler
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