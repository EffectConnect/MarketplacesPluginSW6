<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service;

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

    /**
     * @var SystemConfigService
     */
    protected $_systemConfigService;

    /**
     * SettingsService constructor.
     *
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->_systemConfigService = $systemConfigService;
    }

    /**
     * Get the EffectConnect Marketplaces settings from the system config.
     *
     * @param string|null  $salesChannelId
     * @param Context|null $context
     * @return SettingStruct
     */
    public function getSettings(?string $salesChannelId = null, ?Context $context = null): SettingStruct
    {
        $settings       = new SettingStruct();
        $settingsValues = [];
        $configData     = $this->_systemConfigService->all($salesChannelId);
        $configData     = isset($configData[static::CONFIG_DOMAIN][static::CONFIG_GROUP]) ? $configData[static::CONFIG_DOMAIN][static::CONFIG_GROUP] : [];

        foreach ($configData as $key => $value) {
            $settingsValues[$key]   = $value;
        }

        return $settings->assign($settingsValues);
    }
}