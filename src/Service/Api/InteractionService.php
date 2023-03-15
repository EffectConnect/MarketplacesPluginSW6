<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use CURLFile;
use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Exception\CreateCurlFileException;
use EffectConnect\Marketplaces\Exception\InvalidApiCredentialsException;
use EffectConnect\Marketplaces\Exception\SalesChannelNotFoundException;
use EffectConnect\Marketplaces\Exception\UnknownException;
use EffectConnect\Marketplaces\Factory\SdkFactory;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\PHPSdk\ApiCall;
use EffectConnect\PHPSdk\Core;
use EffectConnect\PHPSdk\Core\Interfaces\ResponseContainerInterface;
use EffectConnect\PHPSdk\Core\Model\Response\Response;
use Exception;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class InteractionService
 * @package EffectConnect\Marketplaces\Service\Api
 */
class InteractionService
{
    /**
     * @var SdkFactory
     */
    protected $_sdkFactory;

    /**
     * @var SettingsService
     */
    protected $_settingsService;

    /**
     * @var SalesChannelService
     */
    protected $_salesChannelService;

    /**
     * @var CredentialService
     */
    protected $_credentialService;

    /**
     * InteractionService constructor.
     *
     * @param SdkFactory $sdkFactory
     * @param SettingsService $settingsService
     * @param SalesChannelService $salesChannelService
     * @param CredentialService $credentialService
     */
    public function __construct(
        SdkFactory $sdkFactory,
        SettingsService $settingsService,
        SalesChannelService $salesChannelService,
        CredentialService $credentialService
    ) {
        $this->_sdkFactory                  = $sdkFactory;
        $this->_settingsService             = $settingsService;
        $this->_salesChannelService         = $salesChannelService;
        $this->_credentialService           = $credentialService;
    }

    /**
     * Get the initialized SDK based on the sales channel.
     *
     * @param string $salesChannelId
     * @return Core
     * @throws InvalidApiCredentialsException
     * @throws UnknownException
     */
    public function getInitializedSdk(string $salesChannelId): Core
    {
        try {
            $context = $this->_salesChannelService->getContext($salesChannelId);
        } catch (SalesChannelNotFoundException $e) {
            $context = Context::createDefaultContext();
        }

        $settings   = $this->_settingsService->getSettings($salesChannelId, $context);
        $publicKey  = $settings->getPublicKey();
        $secretKey  = $settings->getSecretKey();
        $valid      = $this->_credentialService->checkApiCredentials($publicKey, $secretKey);

        if (!$valid) {
            throw new InvalidApiCredentialsException($publicKey ?? '', $secretKey ?? '');
        }

        return $this->_sdkFactory->initializeSdk($publicKey, $secretKey);
    }

    /**
     * Get a CURLFile from a file location.
     *
     * @param string $fileLocation
     * @return CURLFile
     * @throws CreateCurlFileException
     */
    public function getCurlFile(string $fileLocation)
    {
        if (!file_exists($fileLocation)) {
            throw new CreateCurlFileException(sprintf('File does not exist [%s]', $fileLocation));
        }

        try {
            return new CURLFile($fileLocation);
        } catch (Exception $e) {
            throw new CreateCurlFileException($e->getMessage());
        }
    }

    /**
     * Resolve the API call response.
     *
     * @param ApiCall $apiCall
     * @return ResponseContainerInterface
     * @throws ApiCallFailedException
     */
    public function resolveResponse(ApiCall $apiCall): ResponseContainerInterface
    {
        if (!$apiCall->isSuccess())
        {
            $errorMessageString = '[' . implode('] [', $apiCall->getErrors()) . ']';
            throw new ApiCallFailedException($errorMessageString);
        }

        $response   = $apiCall->getResponseContainer();
        $result     = $response->getResponse()->getResult();

        // Check if response is successful
        if ($result == Response::STATUS_FAILURE)
        {
            $errorMessages = [];
            foreach ($response->getErrorContainer()->getErrorMessages() as $errorMessage)
            {
                $errorMessages[] = vsprintf('%s. Code: %s. Message: %s', [
                    $errorMessage->getSeverity(),
                    $errorMessage->getCode(),
                    $errorMessage->getMessage()
                ]);
            }
            $errorMessageString = '[' . implode('] [', $errorMessages) . ']';
            throw new ApiCallFailedException($errorMessageString);
        }

        return $response->getResponse()->getResponseContainer();
    }

    /**
     * Get the SDK factory.
     *
     * @return SdkFactory
     */
    public function getSdkFactory(): SdkFactory
    {
        return $this->_sdkFactory;
    }

    /**
     * Get the settings service.
     *
     * @return SettingsService
     */
    public function getSettingsService(): SettingsService
    {
        return $this->_settingsService;
    }

    /**
     * Get the sales channel service.
     *
     * @return SalesChannelService
     */
    public function getSalesChannelService(): SalesChannelService
    {
        return $this->_salesChannelService;
    }

    /**
     * Get the credential service.
     *
     * @return CredentialService
     */
    public function getCredentialService(): CredentialService
    {
        return $this->_credentialService;
    }
}