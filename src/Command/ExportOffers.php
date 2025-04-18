<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\Api\OfferExportService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Exception;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Class ExportOffers
 * @package EffectConnect\Marketplaces\Command
 */
#[AsCommand(name: 'ec:export-offers')]
class ExportOffers extends AbstractInteractionCommand
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::EXPORT_OFFERS;

    /**
     * @var OfferExportService
     */
    protected $_offerExportService;

    /**
     * ExportOffers constructor.
     *
     * @param OfferExportService $offerExportService
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     * @param string|null $name
     */
    public function __construct(
        OfferExportService $offerExportService,
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory,
        string $name = null
    ) {
        parent::__construct($salesChannelService, $settingsService, $loggerFactory, static::LOGGER_PROCESS, $name);

        $this->_offerExportService = $offerExportService;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDefinition([])
            ->setDescription('EffectConnect Marketplaces - Export Offers')
            ->setHelp("The <info>%command.name%</info> command can be used to export the offers (stock, prices and delivery time) of the different/specified configured sales channels to EffectConnect Marketplaces.")
            ->addArgument('sales-channel-id', InputArgument::OPTIONAL, 'Sales channel to export the offers for (optional).');
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Context|null $context
     */
    protected function execute(InputInterface $input, OutputInterface $output, ?Context $context = null): int
    {
        $this->_logger->info('Executing offer export command started.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        $salesChannelId = $input->getArgument('sales-channel-id');

        try {
            $allSettings = $this->getSettings($salesChannelId);
        } catch (Exception $e) {
            $output->writeln($e->getMessage());

            $this->_logger->emergency('Executing offer export command failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage()
            ]);

            $this->_logger->info('Executing offer export command ended.', [
                'process'       => static::LOGGER_PROCESS
            ]);

            return 0;
        }

        foreach ($allSettings as $settings) {
            $salesChannel = $this->_salesChannelService->getSalesChannel($settings->getSalesChannelId());
            try {
                $this->_offerExportService->exportOffers($salesChannel);

                $output->writeln($this->generateOutputMessage(true, $salesChannel, $settings->getName(), 'Offers Export'));

                $this->_logger->info('Executing offer export command for sales channel succeeded.', [
                    'process'       => static::LOGGER_PROCESS,
                    'connection'    => $settings->getName(),
                    'sales_channel' => [
                        'id'    => $salesChannel->getId(),
                        'name'  => $salesChannel->getName(),
                    ]
                ]);
            } catch (Exception $e) {
                $output->writeln($this->generateOutputMessage(false, $salesChannel, $settings->getName(), 'Offers Export', $e->getMessage()));

                $this->_logger->critical('Executing offer export command for sales channel failed.', [
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

        $this->_logger->info('Executing offer export command ended.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        return 1;
    }
}