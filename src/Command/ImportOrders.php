<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Enum\FulfilmentType;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\Api\OrderImportService;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use Exception;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportOrders
 * @package EffectConnect\Marketplaces\Command
 */
class ImportOrders extends AbstractInteractionCommand
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::IMPORT_ORDERS;

    /**
     * @inheritDoc
     */
    protected static $defaultName = 'ec:import-orders';

    /**
     * @var OrderImportService
     */
    protected $_orderImportService;

    /**
     * ImportOrders constructor.
     *
     * @param OrderImportService $orderImportService
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     * @param string|null $name
     */
    public function __construct(
        OrderImportService $orderImportService,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory,
        string $name = null
    ) {
        parent::__construct($salesChannelService, $settingsService, $loggerFactory, static::LOGGER_PROCESS, $name);

        $this->_orderImportService = $orderImportService;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDefinition([])
            ->setDescription('EffectConnect Marketplaces - Import Orders')
            ->setHelp("The <info>%command.name%</info> command can be used to import the orders for the different/specified configured sales channels to EffectConnect Marketplaces.")
            ->addArgument('sales-channel-id', InputArgument::OPTIONAL, 'Sales channel to import the orders for (optional).');
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Context|null $context
     */
    protected function execute(InputInterface $input, OutputInterface $output, ?Context $context = null)
    {
        $this->_logger->info('Executing order import command started.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        $salesChannelId = $input->getArgument('sales-channel-id');

        try {
            $allSettings = $this->getSettings($salesChannelId);
        } catch (Exception $e) {
            $output->writeln($e->getMessage());

            $this->_logger->emergency('Executing order import command failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage()
            ]);

            $this->_logger->info('Executing order import command ended.', [
                'process'       => static::LOGGER_PROCESS
            ]);

            return 0;
        }

        foreach ($allSettings as $settings) {
            $salesChannel = $this->_salesChannelService->getSalesChannel($settings->getSalesChannelId());
            $this->importOrders($salesChannel, $settings, $output, false);
            if ($settings->isImportExternallyFulfilledOrders()) {
                $this->importOrders($salesChannel, $settings, $output, true);
            }
        }

        $this->_logger->info('Executing order import command ended.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        return 1;
    }

    private function importOrders(SalesChannelEntity $salesChannel, SettingStruct $settings, OutputInterface $output, bool $externallyFulfilled) {
        $fulfilmentType = $externallyFulfilled ? FulfilmentType::EXTERNAL : FulfilmentType::INTERNAL;

        try {
            $this->_orderImportService->importOrders($salesChannel, $externallyFulfilled);

            $output->writeln($this->generateOutputMessage(true, $salesChannel, $settings->getName(), 'Order Import'));

            $this->_logger->info('Executing order import command for sales channel succeeded.', [
                'process'       => static::LOGGER_PROCESS,
                'connection'    => $settings->getName(),
                'fulfilment_type' => $fulfilmentType,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);
        } catch (Exception $e) {
            $output->writeln($this->generateOutputMessage(false, $salesChannel, $settings->getName(), 'Order Import', $e->getMessage()));

            $this->_logger->critical('Executing order import command for sales channel failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'connection'    => $settings->getName(),
                'fulfilment_type' => $fulfilmentType,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);
        }
    }

}