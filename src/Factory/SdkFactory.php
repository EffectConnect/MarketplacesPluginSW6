<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Factory;

use EffectConnect\Marketplaces\Exception\InvalidApiCredentialsException;
use EffectConnect\Marketplaces\Exception\UnknownException;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\PHPSdk\Core;
use EffectConnect\PHPSdk\Core\Exception\InvalidKeyException;
use EffectConnect\PHPSdk\Core\Helper\Keychain;
use Exception;
use Shopware\Core\Framework\Context;

/**
 * Class SdkFactory
 * @package EffectConnect\Marketplaces\Factory
 */
class SdkFactory
{
    /**
     * @var Core
     */
    protected $_sdkCore;

    /**
     * @var SettingsService
     */
    protected $_settingsService;

    /**
     * @var bool
     */
    private $_initialized;

    /**
     * @var string
     */
    private $_publicKey;

    /**
     * @var string
     */
    private $_secretKey;

    /**
     * SdkFactory constructor.
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->_settingsService = $settingsService;
    }

    /**
     * Initialize the SDK using the public and secret keys.
     *
     * @param string $publicKey
     * @param string $secretKey
     * @return Core
     * @throws InvalidApiCredentialsException
     * @throws UnknownException
     */
    public function initializeSdk(string $publicKey, string $secretKey): Core
    {
        $this->_initialized = false;

        try {
            $this->_sdkCore = new Core(
                (new Keychain())
                    ->setPublicKey($publicKey)
                    ->setSecretKey($secretKey)
            );

            $this->_publicKey   = $publicKey;
            $this->_secretKey   = $secretKey;
            $this->_initialized = true;
        } catch (InvalidKeyException $e) {
            throw new InvalidApiCredentialsException($publicKey, $secretKey);
        } catch (Exception $e) {
            throw new UnknownException($e->getMessage());
        }

        return $this->_sdkCore;
    }

    /**
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->_initialized;
    }

    /**
     * Get the SDK based on the public and secret key in the settings.
     *
     * @param string|null $salesChannelId
     * @param Context|null $context
     * @return Core
     * @throws InvalidApiCredentialsException
     * @throws UnknownException
     */
    protected function getSdkBySalesChannel(?string $salesChannelId = null, ?Context $context = null): Core
    {
        $settings       = $this->_settingsService->getSettings($salesChannelId, $context);
        $this->_sdkCore = $this->initializeSdk($settings->getPublicKey(), $settings->getSecretKey());

        return $this->_sdkCore;
    }
}