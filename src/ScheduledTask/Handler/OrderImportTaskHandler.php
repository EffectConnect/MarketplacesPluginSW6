<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Enum\FulfilmentType;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\OrderImportTask;
use EffectConnect\Marketplaces\Service\Api\OrderImportService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use Exception;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class OrderImportTaskHandler
 * @package EffectConnect\Marketplaces\ScheduledTask\Handler
 */
class OrderImportTaskHandler extends AbstractTaskHandler
{
    protected const LOGGER_PROCESS = LoggerProcess::IMPORT_ORDERS;

    /**
     * @var OrderImportService
     */
    protected $_orderImportService;

    /**
     * OrderImportTaskHandler constructor.
     *
     * @param EntityRepository $scheduledTaskRepository
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     * @param OrderImportService $orderImportService
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory,
        OrderImportService $orderImportService
    ) {
        parent::__construct($scheduledTaskRepository, $salesChannelService, $settingsService, $loggerFactory);
        $this->_orderImportService  = $orderImportService;
    }

    /**
     * @inheritDoc
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        return [ OrderImportTask::class ];
    }

    private function importOrders(SalesChannelEntity $salesChannel, SettingStruct $settings, bool $externallyFulfilled) {
        $fulfilmentType = $externallyFulfilled ? FulfilmentType::EXTERNAL : FulfilmentType::INTERNAL;

        try {
            $this->_orderImportService->importOrders($salesChannel, $externallyFulfilled);

            $this->_logger->info('Executing order import task handler for sales channel succeeded.', [
                'process'       => static::LOGGER_PROCESS,
                'fulfilment_type' => $fulfilmentType,
                'connection'    => $settings->getName(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);
        } catch (Exception $e) {
            $this->_logger->critical('Executing order import task handler for sales channel failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'fulfilment_type' => $fulfilmentType,
                'connection'    => $settings->getName(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function runTask(): void
    {
        foreach ($this->_settingsService->getAllSettings() as $settings) {
            $salesChannel = $this->_salesChannelService->getSalesChannel($settings->getSalesChannelId());
            $this->importOrders($salesChannel, $settings, false);
            if ($settings->isImportExternallyFulfilledOrders()) {
                $this->importOrders($salesChannel, $settings, true);
            }
        }

        $this->_logger->info('Executing order import task handler started.', [
            'process'       => static::LOGGER_PROCESS
        ]);
    }
}