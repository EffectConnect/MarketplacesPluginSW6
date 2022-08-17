<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\CatalogExportTask;
use EffectConnect\Marketplaces\Service\Api\CatalogExportService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Exception;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use Throwable;

/**
 * Class CatalogExportTaskHandler
 * @package EffectConnect\Marketplaces\ScheduledTask\Handler
 */
class CatalogExportTaskHandler extends AbstractTaskHandler
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::EXPORT_CATALOG;

    /**
     * @var CatalogExportService
     */
    protected $_catalogExportService;

    /**
     * CatalogExportTaskHandler constructor.
     *
     * @param EntityRepositoryInterface $scheduledTaskRepository
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     * @param CatalogExportService $catalogExportService
     */
    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory,
        CatalogExportService $catalogExportService
    ) {
        parent::__construct($scheduledTaskRepository, $salesChannelService, $settingsService, $loggerFactory);

        $this->_catalogExportService    = $catalogExportService;
        $this->_logger                  = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * @inheritDoc
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [ CatalogExportTask::class ];
    }

    public function run(): void {}

    /**
     * @param CatalogExportTask $task
     * @throws Throwable
     */
    public function handle($task): void
    {
        $taskId = $task->getTaskId();

        if ($taskId === null) {
            $this->_run($task);
            return;
        }

        /** @var ScheduledTaskEntity|null $taskEntity */
        $taskEntity = $this->scheduledTaskRepository
            ->search(new Criteria([$taskId]), Context::createDefaultContext())
            ->get($taskId);

        if ($taskEntity === null || $taskEntity->getStatus() !== ScheduledTaskDefinition::STATUS_QUEUED) {
            return;
        }

        $this->markTaskRunning($task);

        try {
            $this->_run($task);
        } catch (Throwable $e) {
            $this->markTaskFailed($task);
            throw $e;
        }

        $this->rescheduleTask($task, $taskEntity);
    }

    public function _run(CatalogExportTask $task): void
    {
        $this->_logger->info('Executing catalog export task handler started.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        foreach ($this->_salesChannelService->getSalesChannels() as $salesChannel) {
            if ($task->getSalesChannelId() !== null && $task->getSalesChannelId() !== $salesChannel->getId()) {
                continue;
            }

            $settings = $this->_settingsService->getSettings($salesChannel->getId());

            try {
                $this->_catalogExportService->exportCatalog($salesChannel);

                $this->_logger->info('Executing catalog export task handler for sales channel succeeded.', [
                    'process'       => static::LOGGER_PROCESS,
                    'connection'    => $settings->getName(),
                    'sales_channel' => [
                        'id'    => $salesChannel->getId(),
                        'name'  => $salesChannel->getName(),
                    ]
                ]);
            } catch (Exception $e) {
                $this->_logger->critical('Executing catalog export task handler for sales channel failed.', [
                    'process'       => static::LOGGER_PROCESS,
                    'message'       => $e->getMessage(),
                    'connection'    => $settings->getName(),
                    'sales_channel' => [
                        'id'    => $salesChannel->getId(),
                        'name'  => $salesChannel->getName(),
                    ]
                ]);
            }
        }
        $this->_logger->info('Executing catalog export task handler finished.', [
            'process'       => static::LOGGER_PROCESS
        ]);
    }
}