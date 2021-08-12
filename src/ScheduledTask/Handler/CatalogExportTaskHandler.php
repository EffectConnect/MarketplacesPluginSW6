<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\CatalogExportTask;
use EffectConnect\Marketplaces\Service\Api\CatalogExportService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Exception;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

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

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $this->_logger->info('Executing catalog export task handler started.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        /**
         * @var SalesChannelEntity $salesChannel
         */
        foreach ($this->_salesChannelService->getSalesChannels() as $salesChannel) {
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

        $this->_logger->info('Executing catalog export task handler started.', [
            'process'       => static::LOGGER_PROCESS
        ]);
    }
}