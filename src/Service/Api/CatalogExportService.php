<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\Marketplaces\Exception\CatalogExportFailedException;
use EffectConnect\Marketplaces\Exception\CreateCurlFileException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\Transformer\CatalogTransformerService;
use EffectConnect\PHPSdk\Core;
use Exception;
use Monolog\Logger;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class CatalogExportService
 * @package EffectConnect\Marketplaces\Service\Api
 */
class CatalogExportService extends AbstractApiService
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS      = LoggerProcess::EXPORT_CATALOG;

    /**
     * @var CatalogTransformerService
     */
    protected $_catalogTransformerService;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * CatalogExportService constructor.
     *
     * @param InteractionService $interactionService
     * @param CatalogTransformerService $catalogTransformerService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        InteractionService $interactionService,
        CatalogTransformerService $catalogTransformerService,
        LoggerFactory $loggerFactory
    ) {
        parent::__construct($interactionService, $loggerFactory);

        $this->_catalogTransformerService   = $catalogTransformerService;
        $this->_logger                      = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * Export the catalog.
     *
     * @param SalesChannelEntity $salesChannel
     * @return void
     * @throws CatalogExportFailedException
     */
    public function exportCatalog(SalesChannelEntity $salesChannel)
    {
        $this->_logger->info('Export catalog for sales channel started.', [
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

            $this->_logger->info('Export catalog for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            throw new CatalogExportFailedException($salesChannel->getId());
        }

        try {
            $file = $this->_catalogTransformerService
                ->buildCatalogXmlForSalesChannel($salesChannel);
        } catch (Exception $e) {
            $this->_logger->error('Failed to build the catalog XML (or finding the sales channel).', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            $this->_logger->info('Export catalog for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            throw new CatalogExportFailedException($salesChannel->getId());
        }

        try {
            $this->productCreateCall($core, $file);
        } catch (Exception $e) {
            $this->_logger->error('Product Create API call failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            $this->_logger->info('Export catalog for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            throw new CatalogExportFailedException($salesChannel->getId());
        }

        $this->_logger->info('Export catalog for sales channel ended.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ]
        ]);
    }

    /**
     * Create product create call.
     *
     * @param Core $core
     * @param string $file
     * @return void
     * @throws CreateCurlFileException
     * @throws ApiCallFailedException
     */
    protected function productCreateCall(Core $core, string $file)
    {
        $productsCall   = $core->ProductsCall();
        $curlFile       = $this->_interactionService->getCurlFile($file);
        $apiCall        = $productsCall->create($curlFile);

        $apiCall->call();

        $this->_interactionService->resolveResponse($apiCall);
    }
}