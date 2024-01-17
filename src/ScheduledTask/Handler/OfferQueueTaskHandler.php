<?php

namespace EffectConnect\Marketplaces\ScheduledTask\Handler;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\OfferQueueTask;
use EffectConnect\Marketplaces\Service\OfferQueueService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class OfferQueueTaskHandler extends AbstractTaskHandler
{
    protected const LOGGER_PROCESS = LoggerProcess::OFFER_CHANGE_TASK;

    /**
     * @var OfferQueueService
     */
    private $offerQueueService;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
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

    public function runTask(): void
    {
        $this->offerQueueService->run();
    }
}