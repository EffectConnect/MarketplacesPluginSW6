<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Exception\SalesChannelNotFoundException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\CatalogExportTask;
use EffectConnect\Marketplaces\Service\Api\CatalogExportService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Exception;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
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
     * @param EntityRepository $scheduledTaskRepository
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     * @param CatalogExportService $catalogExportService
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory,
        CatalogExportService $catalogExportService
    ) {
        parent::__construct($scheduledTaskRepository, $salesChannelService, $settingsService, $loggerFactory);

        $this->_catalogExportService    = $catalogExportService;
    }

    /**
     * @inheritDoc
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [ CatalogExportTask::class ];
    }

    public function runTask(): void
    {
        foreach ($this->_settingsService->getAllSettings() as $settings) {
            try {
                $salesChannel = $this->_salesChannelService->getSalesChannel($settings->getSalesChannelId());
            } catch (SalesChannelNotFoundException $e) {
                continue;
            }

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
    }
}