<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\OfferExportTask;
use EffectConnect\Marketplaces\Service\Api\OfferExportService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Exception;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class OfferExportTaskHandler
 * @package EffectConnect\Marketplaces\ScheduledTask\Handler
 */
class OfferExportTaskHandler extends AbstractTaskHandler
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::EXPORT_OFFERS;

    /**
     * @var OfferExportService
     */
    protected $_offerExportService;

    /**
     * OfferExportTaskHandler constructor.
     *
     * @param EntityRepositoryInterface $scheduledTaskRepository
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     * @param OfferExportService $offerExportService
     */
    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory,
        OfferExportService $offerExportService
    ) {
        parent::__construct($scheduledTaskRepository, $salesChannelService, $settingsService, $loggerFactory);

        $this->_offerExportService  = $offerExportService;
        $this->_logger              = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * @inheritDoc
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [ OfferExportTask::class ];
    }

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $this->_logger->info('Executing offer export task handler started.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        /**
         * @var SalesChannelEntity $salesChannel
         */
        foreach ($this->_salesChannelService->getSalesChannels() as $salesChannel) {
            $settings = $this->_settingsService->getSettings($salesChannel->getId());

            try {
                $this->_offerExportService->exportOffers($salesChannel);

                $this->_logger->info('Executing offer export task handler for sales channel succeeded.', [
                    'process'       => static::LOGGER_PROCESS,
                    'connection'    => $settings->getName(),
                    'sales_channel' => [
                        'id'    => $salesChannel->getId(),
                        'name'  => $salesChannel->getName(),
                    ]
                ]);
            } catch (Exception $e) {
                $this->_logger->critical('Executing offer export task handler for sales channel failed.', [
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

        $this->_logger->info('Executing offer export task handler ended.', [
            'process'       => static::LOGGER_PROCESS
        ]);
    }
}