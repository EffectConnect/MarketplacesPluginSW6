<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service;

use EffectConnect\Marketplaces\Core\Connection\ConnectionEntity;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class SettingsService
 * @package EffectConnect\Marketplaces\Service
 */
class SettingsService
{
    /**
     * The configuration domain for the EffectConnect Marketplaces plugin configuration.
     */
    public const CONFIG_DOMAIN = 'EffectConnectMarketplaces';

    /**
     * The configuration group for the EffectConnect Marketplaces plugin configuration.
     */
    public const CONFIG_GROUP = 'config';

    protected $systemConfigService;
    protected $connectionService;

    /**
     * SettingsService constructor.
     *
     * @param SystemConfigService $systemConfigService
     * @param ConnectionService $connectionService
     */
    public function __construct(SystemConfigService $systemConfigService, ConnectionService $connectionService)
    {
        $this->systemConfigService = $systemConfigService;
        $this->connectionService = $connectionService;
    }

    /**
     * Get the EffectConnect Marketplaces settings
     *
     * @param string  $salesChannelId
     * @param Context|null $context
     * @return SettingStruct
     */
    public function getSettings(string $salesChannelId, ?Context $context = null): SettingStruct
    {
        $connection = $this->connectionService->get($salesChannelId);
        if ($connection !== null) {
            return (new SettingStruct($connection));
        }

        $connection = new ConnectionEntity();
        $connection->setSalesChannelId($salesChannelId);
        return (new SettingStruct($connection));
    }

    /**
     * @return SettingStruct[]
     */
    public function getAllSettings(): array
    {
        $settings = [];
        foreach($this->connectionService->getAll() as $connection) {
            $settings[] = new SettingStruct($connection);
        }
        return $settings;
    }
}