<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\Api\CatalogExportService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Exception;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportCatalog
 * @package EffectConnect\Marketplaces\Command
 */
class ExportCatalog extends AbstractInteractionCommand
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::EXPORT_CATALOG;

    /**
     * @inheritDoc
     */
    protected static $defaultName = 'ec:export-catalog';

    /**
     * @var CatalogExportService
     */
    protected $_catalogExportService;

    /**
     * ExportCatalog constructor.
     *
     * @param CatalogExportService $catalogExportService
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     * @param string|null $name
     */
    public function __construct(
        CatalogExportService $catalogExportService,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory,
        string $name = null
    ) {
        parent::__construct($salesChannelService, $settingsService, $loggerFactory, static::LOGGER_PROCESS, $name);

        $this->_catalogExportService = $catalogExportService;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDefinition([])
            ->setDescription('EffectConnect Marketplaces - Export Catalog')
            ->setHelp("The <info>%command.name%</info> command can be used to export the catalog of the different/specified configured sales channels to EffectConnect Marketplaces.")
            ->addArgument('sales-channel-id', InputArgument::OPTIONAL, 'Sales channel to export the catalog for (optional).');
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Context|null $context
     */
    protected function execute(InputInterface $input, OutputInterface $output, ?Context $context = null)
    {
        $this->_logger->info('Executing catalog export command started.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        $salesChannelId = $input->getArgument('sales-channel-id');

        try {
            $allSettings = $this->getSettings($salesChannelId);
        } catch (Exception $e) {
            $output->writeln($e->getMessage());

            $this->_logger->emergency('Executing catalog export command failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage()
            ]);

            $this->_logger->info('Executing catalog export command ended.', [
                'process'       => static::LOGGER_PROCESS
            ]);

            return 0;
        }

        foreach ($allSettings as $settings) {
            try {
                $salesChannel = $this->_salesChannelService->getSalesChannel($settings->getSalesChannelId());
                $this->_catalogExportService->exportCatalog($salesChannel);

                $output->writeln($this->generateOutputMessage(true, $salesChannel, $settings->getName(), 'Catalog Export'));

                $this->_logger->info('Executing catalog export command for sales channel succeeded.', [
                    'process'       => static::LOGGER_PROCESS,
                    'connection'    => $settings->getName(),
                    'sales_channel' => [
                        'id'    => $salesChannel->getId(),
                        'name'  => $salesChannel->getName(),
                    ]
                ]);
            } catch (Exception $e) {
                $output->writeln($this->generateOutputMessage(false, $salesChannel, $settings->getName(), 'Catalog Export', $e->getMessage()));

                $this->_logger->critical('Executing catalog export command for sales channel failed.', [
                    'process'       => static::LOGGER_PROCESS,
                    'message'       => $e->getMessage(),
                    'connection'    => $settings->getName(),
                    'sales_channel' => [
                        'id'    => $salesChannel->getId(),
                        'name'  => $salesChannel->getName(),
                    ]
                ]);
            }
        }

        $this->_logger->info('Executing catalog export command ended.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        return 1;
    }
}