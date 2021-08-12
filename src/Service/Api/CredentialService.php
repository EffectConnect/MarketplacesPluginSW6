<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use EffectConnect\Marketplaces\Exception\InvalidApiCredentialsException;
use EffectConnect\Marketplaces\Exception\UnknownException;
use EffectConnect\Marketplaces\Factory\SdkFactory;

/**
 * Class CredentialService
 * @package EffectConnect\Marketplaces\Service\Api
 */
class CredentialService
{
    /**
     * @var SdkFactory
     */
    protected $_sdkFactory;

    /**
     * CredentialService constructor.
     *
     * @param SdkFactory $sdkFactory
     */
    public function __construct(SdkFactory $sdkFactory)
    {
        $this->_sdkFactory = $sdkFactory;
    }

    /**
     * Check if API credentials are valid.
     *
     * @param string $publicKey
     * @param string $secretKey
     * @return bool
     */
    public function checkApiCredentials(string $publicKey, string $secretKey): bool
    {
        try {
            $this->_sdkFactory->initializeSdk($publicKey, $secretKey);
            return true;
        } catch (InvalidApiCredentialsException $e) {
            return false;
        } catch (UnknownException $e) {
            return false;
        }
    }
}