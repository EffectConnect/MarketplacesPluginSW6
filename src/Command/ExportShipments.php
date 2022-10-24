<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Core\ExportQueue\Data\OrderExportQueueData;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueType;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\Api\ShippingExportService;
use EffectConnect\Marketplaces\Service\ExportQueueService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Exception;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class ExportShipments
 * @package EffectConnect\Marketplaces\Command
 */
class ExportShipments extends AbstractInteractionCommand
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::EXPORT_SHIPMENT_TASK;

    /**
     * @inheritDoc
     */
    protected static $defaultName = 'ec:export-shipments';

    /** @var ExportQueueService */
    private $exportQueueService;

    /** @var ShippingExportService */
    private $shippingExportService;

    /**
     * @param ExportQueueService $exportQueueService
     * @param ShippingExportService $shippingExportService
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        ExportQueueService $exportQueueService,
        ShippingExportService $shippingExportService,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory)
    {
        parent::__construct($salesChannelService, $settingsService, $loggerFactory);
        $this->exportQueueService = $exportQueueService;
        $this->shippingExportService = $shippingExportService;
        $this->_logger = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDefinition([])
            ->setDescription('EffectConnect Marketplaces - Export Shipments')
            ->setHelp("The <info>%command.name%</info> command can be used to export the shipments that are queued to EffectConnect Marketplaces.")
            ->addArgument('sales-channel-id', InputArgument::OPTIONAL, 'Sales channel to check the API credentials for.')
        ;
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

    /**
     * @param SalesChannelEntity $salesChannel
     * @param OutputInterface $output
     * @param Context|null $context
     * @return void
     */
    private function executeFor(SalesChannelEntity $salesChannel, OutputInterface $output, ?Context $context = null) {
        $settings = $this->_settingsService->getSettings($salesChannel->getId(), $context);

        $queueList = $this->exportQueueService->getInQueue($salesChannel->getId(), ExportQueueType::SHIPMENT);

        if (count($queueList) === 0) {
            $this->_logger->info('No queue items to export.', $this->getLogContext($salesChannel));
            $output->writeln($this->generateOutputMessage(true, $salesChannel, $settings->getName(), 'Shipments Export'));
            return;
        }

        $ids = array_values(array_map(function($q) {return $q->getId();}, $queueList));
        $this->exportQueueService->start($ids);
        $this->_logger->info(count($ids) . ' queue items started exporting.', $this->getLogContext($salesChannel));
        try {
            $orderExportDataList = array_map(function($q) {return OrderExportQueueData::fromArray($q->getData());}, $queueList);
            $lineDeliveries = [];
            foreach($orderExportDataList as $data) {
                foreach($data->getLineDeliveries() as $lineDelivery) {
                    $lineDeliveries[] = $lineDelivery;
                }
            }
            $this->shippingExportService->exportShipment($salesChannel, $lineDeliveries);
            $this->_logger->info(count($ids) . ' queue items exported.', $this->getLogContext($salesChannel));
        } catch (Throwable $e) {
            $this->_logger->error(count($ids) . ' queue items failed to export.',
                $this->getLogContext($salesChannel, ['message' => $e->getMessage()])
            );
        } finally {
            $this->exportQueueService->complete($ids);
        }

        $output->writeln($this->generateOutputMessage(true, $salesChannel, $settings->getName(), 'Shipments Export'));

        $this->_logger->info('Executing shipment export command for sales channel succeeded.', $this->getLogContext($salesChannel));
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Context|null $context
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output, ?Context $context = null): int
    {
        $this->_logger->info('Executing export shipments command started.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        /**
         * @var SalesChannelEntity $salesChannel
         */
        foreach ($this->getSalesChannels($input->getArgument('sales-channel-id')) as $salesChannel) {
            try {
                $this->executeFor($salesChannel, $output, $context);
            } catch (Throwable $e) {
                $output->writeln($this->generateOutputMessage(false, $salesChannel, $salesChannel->getName(), 'Shipments Export', $e->getMessage()));
                $this->_logger->critical('Executing export shipments command for sales channel failed.',
                    $this->getLogContext($salesChannel, ['message' => $e->getMessage()])
                );
            }
        }

        $this->_logger->info('Executing export shipments command ended.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        return 1;
    }
}