<?php

namespace EffectConnect\Marketplaces\Service;

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
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ShipmentQueueService extends AbstractQueueService
{
    const LOGGER_PROCESS = LoggerProcess::EXPORT_SHIPMENT_TASK;

    /**
     * @var ShippingExportService
     */
    protected $shippingExportService;

    public function __construct(
        ExportQueueService $exportQueueService,
        ShippingExportService $shippingExportService,
        SalesChannelService $salesChannelService,
        LoggerFactory $loggerFactory)
    {
        parent::__construct($exportQueueService, $salesChannelService, $loggerFactory);
        $this->shippingExportService = $shippingExportService;
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