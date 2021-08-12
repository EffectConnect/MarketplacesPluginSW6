<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Exception\SalesChannelNotFoundException;
use EffectConnect\Marketplaces\Object\SalesChannelCheckApiCredentialResult;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\Api\CredentialService;
use EffectConnect\Marketplaces\Service\SettingsService;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckApiCredentials
 * @package EffectConnect\Marketplaces\Command
 */
class CheckApiCredentials extends Command
{
    /**
     * @inheritDoc
     */
    protected static $defaultName = 'ec:check-api-credentials';

    /**
     * @var SettingsService
     */
    protected $_settingsService;

    /**
     * @var CredentialService
     */
    protected $_credentialService;

    /**
     * @var SalesChannelService
     */
    protected $_salesChannelService;

    /**
     * CheckApiCredentials constructor.
     *
     * @param SettingsService $settingsService
     * @param CredentialService $credentialService
     * @param SalesChannelService $salesChannelService
     * @param string|null $name
     */
    public function __construct(
        SettingsService $settingsService,
        CredentialService $credentialService,
        SalesChannelService $salesChannelService,
        string $name = null
    ) {
        parent::__construct($name);

        $this->_settingsService         = $settingsService;
        $this->_credentialService    = $credentialService;
        $this->_salesChannelService     = $salesChannelService;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDefinition([])
            ->setDescription('EffectConnect Marketplaces - Check API Credentials')
            ->setHelp("The <info>%command.name%</info> command can be used to check if the API credentials for the different/specified sales channels are valid to use with the EffectConnect Marketplaces API.")
            ->addArgument('sales-channel-id', InputArgument::OPTIONAL, 'Sales channel to check the API credentials for (optional).');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output, ?Context $context = null)
    {
        $salesChannelId = $input->getArgument('sales-channel-id');
        $results        = [true => [], false => []];
        $salesChannels  = [];

        if (is_null($salesChannelId)) {
            $salesChannels = $this->_salesChannelService->getSalesChannels();
        } else {
            try {
                $salesChannels = [$this->_salesChannelService->getSalesChannel($salesChannelId)];
            } catch (SalesChannelNotFoundException $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                return 0;
            }
        }

        if (is_null($salesChannels) || empty($salesChannels)) {
            $output->writeln(sprintf('<error>No sales channels found.</error>'));
        }

        /**
         * @var SalesChannelEntity $salesChannel
         */
        foreach ($salesChannels as $salesChannel) {
            $result = $this->checkSalesChannel($salesChannel);
            $results[$result->isValid()][] = $result->getMessage();
        }

        foreach ($results as $result) {
            $output->writeln($result);
        }

        return 1;
    }

    /**
     * Check if the API credentials for a specific sales channel are valid.
     *
     * @param SalesChannelEntity $salesChannel
     * @return SalesChannelCheckApiCredentialResult
     */
    protected function checkSalesChannel(SalesChannelEntity $salesChannel): SalesChannelCheckApiCredentialResult
    {
        try {
            $context = $this->_salesChannelService->getContext($salesChannel->getId());
        } catch (SalesChannelNotFoundException $e) {
            $context = Context::createDefaultContext();
        }

        $settings   = $this->_settingsService->getSettings($salesChannel->getId(), $context);
        $publicKey  = $settings->getPublicKey();
        $secretKey  = $settings->getSecretKey();
        $valid      = $this->_credentialService->checkApiCredentials($publicKey, $secretKey);
        $validText  = $valid ? '<info>[ VALID ]</info>' : '<fg=red>[INVALID]</>';
        $message    = sprintf(
            '%s: %s (ID: <comment>%s</comment>) - <fg=cyan>[Public Key: "%s" | Secret Key: "%s"]</>',
            $validText,
            $salesChannel->getName(),
            $salesChannel->getId(),
            $publicKey,
            $secretKey
        );

        return new SalesChannelCheckApiCredentialResult($valid, $message);
    }
}