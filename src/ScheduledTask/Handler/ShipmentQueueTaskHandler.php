<?php

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Core\ExportQueue\Data\OrderExportQueueData;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueEntity;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueType;
use EffectConnect\Marketplaces\Exception\ShipmentExportFailedException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\ShipmentQueueTask;
use EffectConnect\Marketplaces\Service\Api\ShippingExportService;
use EffectConnect\Marketplaces\Service\ExportQueueService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ShipmentQueueTaskHandler extends AbstractQueueTaskHandler
{
    const LOGGER_PROCESS = LoggerProcess::EXPORT_SHIPMENT_TASK;

    /**
     * @var ShippingExportService
     */
    protected $shippingExportService;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ExportQueueService $exportQueueService,
        ShippingExportService $shippingExportService,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory)
    {
        parent::__construct($scheduledTaskRepository, $exportQueueService, $salesChannelService, $settingsService, $loggerFactory);
        $this->shippingExportService = $shippingExportService;
    }

    public static function getHandledMessages(): iterable
    {
        return [ShipmentQueueTask::class];
    }

    /**
     * @param ExportQueueEntity[] $queueList
     * @param SalesChannelEntity $salesChannel
     * @return void
     * @throws ShipmentExportFailedException
     */
    protected function processQueueList(array $queueList, SalesChannelEntity $salesChannel)
    {
        $orderExportDataList = array_map(function($q) {return OrderExportQueueData::fromArray($q->getData());}, $queueList);
        $lineDeliveries = [];
        foreach($orderExportDataList as $data) {
            foreach($data->getLineDeliveries() as $lineDelivery) {
                $lineDeliveries[] = $lineDelivery;
            }
        }
        $this->shippingExportService->exportShipment($salesChannel, $lineDeliveries);
    }

    protected function getExportQueueType(): string
    {
        return ExportQueueType::SHIPMENT;
    }
}