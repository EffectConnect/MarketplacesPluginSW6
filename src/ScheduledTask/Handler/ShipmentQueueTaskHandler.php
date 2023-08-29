<?php

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\ShipmentQueueTask;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\Marketplaces\Service\ShipmentQueueService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class ShipmentQueueTaskHandler extends AbstractTaskHandler
{
    protected const LOGGER_PROCESS = LoggerProcess::EXPORT_SHIPMENT_TASK;


    /**
     * @var ShipmentQueueService
     */
    private $shipmentQueueService;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ShipmentQueueService      $shipmentQueueService,
        SalesChannelService       $salesChannelService,
        SettingsService           $settingsService,
        LoggerFactory             $loggerFactory)
    {
        parent::__construct($scheduledTaskRepository, $salesChannelService, $settingsService, $loggerFactory);
        $this->shipmentQueueService = $shipmentQueueService;
    }

    public static function getHandledMessages(): iterable
    {
        return [ShipmentQueueTask::class];
    }


    public function runTask(): void
    {
        $this->shipmentQueueService->run();
    }
}