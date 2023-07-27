<?php

namespace EffectConnect\Marketplaces\Service;

use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueEntity;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\ScheduledTask\Handler\AbstractTaskHandler;
use EffectConnect\Marketplaces\Service\ExportQueueService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Monolog\Logger;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Throwable;

abstract class AbstractQueueService
{
    const LOGGER_PROCESS = LoggerProcess::OTHER;

    /**
     * @var ExportQueueService
     */
    private $exportQueueService;

    /**
     * @var SalesChannelService
     */
    private $salesChannelService;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ExportQueueService $exportQueueService
     * @param SalesChannelService $salesChannelService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        ExportQueueService $exportQueueService,
        SalesChannelService $salesChannelService,
        LoggerFactory $loggerFactory)
    {
        $this->exportQueueService = $exportQueueService;
        $this->salesChannelService = $salesChannelService;
        $this->logger = $loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    private function getLogContext(SalesChannelEntity $salesChannel, array $additions = []): array
    {
        return array_merge([
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ]
        ], $additions);
    }

    public function run(): void
    {
        foreach ($this->salesChannelService->getSalesChannels() as $salesChannel) {
            $queueList = $this->exportQueueService->getInQueue($salesChannel->getId(), $this->getExportQueueType(), $this->getLimit());

            if (count($queueList) === 0) {
                $this->logger->info('No queue items to export.', $this->getLogContext($salesChannel));
                continue;
            }

            $ids = array_values(array_map(function($q) {return $q->getId();}, $queueList));
            $this->exportQueueService->start($ids);
            $this->logger->info(count($ids) . ' queue items started exporting.', $this->getLogContext($salesChannel));
            try {
                $this->processQueueList($queueList, $salesChannel);
                $this->logger->info(count($ids) . ' queue items exported.', $this->getLogContext($salesChannel));
            } catch (Throwable $e) {
                $this->logger->error(count($ids) . ' queue items failed to export.',
                    $this->getLogContext($salesChannel, ['message' => $e->getMessage()])
                );
            } finally {
                $this->exportQueueService->complete($ids);
            }
        }
    }

    protected function getLimit(): ?int {
        return null;
    }

    /**
     * @param ExportQueueEntity[] $queueList
     * @param SalesChannelEntity $salesChannel
     * @return void
     * @throws Throwable
     */
    protected abstract function processQueueList(array $queueList, SalesChannelEntity $salesChannel);

    protected abstract function getExportQueueType(): string;
}