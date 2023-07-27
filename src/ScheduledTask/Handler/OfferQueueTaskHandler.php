<?php

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\ScheduledTask\OfferQueueTask;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class OfferQueueTaskHandler extends AbstractTaskHandler
{
    /**
     * @var OfferQueueService
     */
    private $offerQueueService;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        OfferQueueService         $offerQueueService,
        SalesChannelService       $salesChannelService,
        SettingsService           $settingsService,
        LoggerFactory             $loggerFactory)
    {
        parent::__construct($scheduledTaskRepository, $salesChannelService, $settingsService, $loggerFactory);
        $this->offerQueueService = $offerQueueService;
    }

    public static function getHandledMessages(): iterable
    {
        return [OfferQueueTask::class];
    }

    public function run(): void
    {
        $this->offerQueueService->run();
    }
}