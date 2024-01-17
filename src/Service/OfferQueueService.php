<?php

namespace EffectConnect\Marketplaces\Service;

use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueEntity;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueType;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\OfferQueueTask;
use EffectConnect\Marketplaces\Service\Api\OfferQueueExportService;
use EffectConnect\Marketplaces\Service\ExportQueueService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\Marketplaces\Service\Transformer\OfferTransformerService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Throwable;

class OfferQueueService extends AbstractQueueService
{
    const LOGGER_PROCESS = LoggerProcess::OFFER_CHANGE_TASK;

    /**
     * @var OfferQueueExportService
     */
    private $offerQueueExportService;

    public function __construct(
        ExportQueueService $exportQueueService,
        OfferQueueExportService $offerQueueExportService,
        SalesChannelService $salesChannelService,
        LoggerFactory $loggerFactory)
    {
        parent::__construct($exportQueueService, $salesChannelService, $loggerFactory);
        $this->offerQueueExportService = $offerQueueExportService;
    }

    /**
     * @param ExportQueueEntity[] $queueList
     * @param SalesChannelEntity $salesChannel
     * @return void
     * @throws Throwable
     */
    protected function processQueueList(array $queueList, SalesChannelEntity $salesChannel)
    {
        $productIds = array_values(array_map(function($q) {return $q->getIdentifier();}, $queueList));
        $this->offerQueueExportService->exportOffers($salesChannel, $productIds);
    }

    protected function getExportQueueType(): string
    {
        return ExportQueueType::OFFER;
    }
}