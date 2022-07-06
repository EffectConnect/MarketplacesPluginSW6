<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Exception\CreateCurlFileException;
use EffectConnect\Marketplaces\Exception\OfferExportFailedException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\Transformer\OfferTransformerService;
use EffectConnect\PHPSdk\Core;
use Exception;
use Monolog\Logger;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class OfferExportService
 * @package EffectConnect\Marketplaces\Service\Api
 */
class OfferExportService extends AbstractApiService
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS      = LoggerProcess::EXPORT_OFFERS;

    /**
     * @var OfferTransformerService
     */
    protected $_offerTransformerService;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * OfferExportService constructor.
     *
     * @param InteractionService $interactionService
     * @param OfferTransformerService $offerTransformerService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        InteractionService $interactionService,
        OfferTransformerService $offerTransformerService,
        LoggerFactory $loggerFactory
    ) {
        parent::__construct($interactionService, $loggerFactory);

        $this->_offerTransformerService     = $offerTransformerService;
        $this->_logger                      = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * Export the offers.
     *
     * @param SalesChannelEntity $salesChannel
     * @return void
     * @throws OfferExportFailedException
     */
    public function exportOffers(SalesChannelEntity $salesChannel, ?array $productIds = null)
    {
        $this->_logger->info('Export offers for sales channel started.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ]
        ]);

        try {
            $core = $this->_interactionService
                ->getInitializedSdk($salesChannel->getId());
        } catch (Exception $e) {
            $this->_logger->error('Failed to initialize SDK (or connect to the API).', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            $this->_logger->info('Export offers for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            throw new OfferExportFailedException($salesChannel->getId());
        }

        try {
            $file = $this->_offerTransformerService->buildOfferXmlForSalesChannel($salesChannel, $productIds);
        } catch (Exception $e) {
            $this->_logger->error('Failed to build the offers XML (or finding the sales channel).', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            $this->_logger->info('Export offers for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            throw new OfferExportFailedException($salesChannel->getId());
        }

        try {
            $this->productUpdateCall($core, $file);
        } catch (Exception $e) {
            $this->_logger->error('Product Update API call failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            $this->_logger->info('Export offers for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            throw new OfferExportFailedException($salesChannel->getId());
        }

        $this->_logger->info('Export offers for sales channel ended.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ]
        ]);
    }

    /**
     * Create the product update call.
     *
     * @param Core $core
     * @param string $file
     * @return void
     * @throws ApiCallFailedException
     * @throws CreateCurlFileException
     */
    protected function productUpdateCall(Core $core, string $file)
    {
        $productsCall   = $core->ProductsCall();
        $curlFile       = $this->_interactionService->getCurlFile($file);
        $apiCall        = $productsCall->update($curlFile);

        $apiCall->call();

        $this->_interactionService->resolveResponse($apiCall);
    }
}