<?php

namespace EffectConnect\Marketplaces\Service;

use EffectConnect\Marketplaces\Core\Connection\ConnectionEntity;
use EffectConnect\Marketplaces\Helper\DefaultSettingHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class SettingMigrationService
{
    protected $systemConfigService;
    protected $connectionService;
    protected $salesChannelRepository;

    /**
     * SettingMigrationService constructor.
     *
     * @param SystemConfigService $systemConfigService
     * @param ConnectionService $connectionService
     * @param EntityRepositoryInterface $salesChannelRepository
     */
    public function __construct(SystemConfigService $systemConfigService, ConnectionService $connectionService, EntityRepositoryInterface $salesChannelRepository)
    {
        $this->systemConfigService = $systemConfigService;
        $this->connectionService = $connectionService;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public function migrate() {
        $salesChannels = $this->salesChannelRepository->search(new Criteria(), Context::createDefaultContext());

        foreach($salesChannels->getEntities()->getElements() as $salesChannel) {
            $this->migrateFor($salesChannel->getId());
        }
    }

    /**
     * @param string void
     */
    private function migrateFor(string $salesChannelId) {
        if ($this->connectionService->exists($salesChannelId)) {
            return;
        }

        $configData = $this->systemConfigService->all($salesChannelId)[SettingsService::CONFIG_DOMAIN][SettingsService::CONFIG_GROUP] ?? [];
        if (count($configData) === 0) {
            return;
        }
        $settingsValues = [];
        foreach ($configData as $key => $value) {
            if (in_array($key, ['offerExportSchedule', 'orderImportSchedule', 'catalogExportSchedule'])) {
                $value = (int)$value;
            }
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