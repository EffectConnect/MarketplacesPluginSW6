<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Object\SalesChannelCheckApiCredentialResult;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\Api\CredentialService;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckApiCredentials
 * @package EffectConnect\Marketplaces\Command
 */
#[AsCommand(name: 'ec:check-api-credentials')]
class CheckApiCredentials extends Command
{
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
    protected function execute(InputInterface $input, OutputInterface $output, ?Context $context = null): int
    {
        $salesChannelId = $input->getArgument('sales-channel-id');
        $results        = [true => [], false => []];
        if ($salesChannelId === null) {
            $settings = $this->_settingsService->getAllSettings();
        } else {
            $settings = [$this->_settingsService->getSettings($salesChannelId)];
        }

        foreach ($settings as $setting) {
            $salesChannel = $this->_salesChannelService->getSalesChannel($setting->getSalesChannelId());
            $result = $this->checkSalesChannel($setting, $salesChannel);
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
     * @param SettingStruct $settings
     * @param SalesChannelEntity $salesChannel
     * @return SalesChannelCheckApiCredentialResult
     */
    protected function checkSalesChannel(SettingStruct $settings, SalesChannelEntity $salesChannel): SalesChannelCheckApiCredentialResult
    {
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