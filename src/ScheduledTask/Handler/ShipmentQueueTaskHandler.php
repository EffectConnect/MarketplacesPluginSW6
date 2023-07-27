<?php

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\ScheduledTask\ShipmentQueueTask;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class ShipmentQueueTaskHandler extends AbstractTaskHandler
{
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


    public function run(): void
    {
        $this->shipmentQueueService->run();
    }
}