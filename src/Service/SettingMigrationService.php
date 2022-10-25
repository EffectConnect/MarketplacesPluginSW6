<?php

namespace EffectConnect\Marketplaces\Service;

use EffectConnect\Marketplaces\Core\Connection\ConnectionEntity;
use EffectConnect\Marketplaces\Helper\DefaultSettingHelper;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class SettingMigrationService
{
    private const FLAG_DIR =  __DIR__ . '/../../data';
    private const MIGRATION_FILE_PATH = self::FLAG_DIR . '/settings_migrated';

    protected $systemConfigService;
    protected $connectionService;
    protected $salesChannelService;

    /**
     * SettingsService constructor.
     *
     * @param SystemConfigService $systemConfigService
     * @param ConnectionService $connectionService
     * @param SalesChannelService $salesChannelService
     */
    public function __construct(SystemConfigService $systemConfigService, ConnectionService $connectionService, SalesChannelService $salesChannelService)
    {
        $this->systemConfigService = $systemConfigService;
        $this->connectionService = $connectionService;
        $this->salesChannelService = $salesChannelService;
    }

    public function isMigrated(): bool
    {
        return file_exists(self::MIGRATION_FILE_PATH);
    }

    public function migrate() {
        foreach($this->salesChannelService->getSalesChannels() as $salesChannel) {
            $this->migrateFor($salesChannel->getId());
        }
        $success = touch(self::MIGRATION_FILE_PATH);
    }

    /**
     * @param string void
     */
    private function migrateFor(string $salesChannelId) {
        $connection = $this->connectionService->get($salesChannelId);
        if ($connection !== null) {
            return;
        }

        $configData = $this->systemConfigService->all($salesChannelId)[SettingsService::CONFIG_DOMAIN][SettingsService::CONFIG_GROUP] ?? [];
        if (count($configData) === 0) {
            return;
        }
        $settingsValues = [];
        foreach ($configData as $key => $value) {
            $settingsValues[$key] = $value;
        }
        foreach(DefaultSettingHelper::getDefaults() as $key => $value) {
            if (!isset($settingsValues[$key])) {
                $settingsValues[$key] = $value;
            }
        }
        if (empty($settingsValues['name'])) {
            $settingsValues['name'] = ' - ';
        }
        $connection = new ConnectionEntity();
        $connection->setSalesChannelId($salesChannelId);
        $connection->assign($settingsValues);
        $this->connectionService->create($connection);
    }
}