<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Command;

use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use Monolog\Logger;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\Console\Command\Command;

/**
 * Class ExportCatalog
 * @package EffectConnect\Marketplaces\Command
 */
abstract class AbstractInteractionCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected static $defaultName   = 'ec:export-catalog';

    /**
     * @var SalesChannelService
     */
    protected $_salesChannelService;

    /**
     * @var SettingsService
     */
    protected $_settingsService;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var LoggerFactory
     */
    protected $_loggerFactory;

    /**
     * AbstractInteractionCommand constructor.
     *
     * @param SalesChannelService $salesChannelService
     * @param SettingsService $settingsService
     * @param LoggerFactory $loggerFactory
     * @param string $loggerProcess
     * @param string|null $name
     */
    public function __construct(
        SalesChannelService $salesChannelService,
        SettingsService $settingsService,
        LoggerFactory $loggerFactory,
        string $loggerProcess = LoggerProcess::OTHER,
        string $name = null
    ) {
        parent::__construct($name);

        $this->_salesChannelService = $salesChannelService;
        $this->_settingsService     = $settingsService;
        $this->_loggerFactory       = $loggerFactory;
        $this->_logger              = $this->_loggerFactory::createLogger($loggerProcess);
    }

    /**
     * @param string|null $salesChannelId
     * @return SettingStruct[]
     */
    protected function getSettings(?string $salesChannelId = null): array
    {
        if ($salesChannelId === null) {
            return $this->_settingsService->getAllSettings();
        } else {
            return [$this->_settingsService->getSettings($salesChannelId)];
        }
    }

    /**
     * Generate a result message for a certain action on a certain sales channel.
     *
     * @param bool $success
     * @param SalesChannelEntity $salesChannel
     * @param string $name
     * @param string $action
     * @param string $errorMessage
     * @return string
     */
    protected function generateOutputMessage(bool $success, SalesChannelEntity $salesChannel, string $name, string $action, string $errorMessage = '')
    {
        return sprintf(
            '<fg=%s>[%s]</>%s: %s (ID: <comment>%s</comment>) - <fg=cyan>[%s]</>',
            ($success ? 'green' : 'red'),
            ($success ? 'SUCCESS' : ' ERROR '),
            (!empty($name) ? (' (' . $name . ')') : ''),
            $salesChannel->getName(),
            $salesChannel->getId(),
            $action
        ) . (!empty($errorMessage) ? ': ' . $errorMessage : '');
    }
}