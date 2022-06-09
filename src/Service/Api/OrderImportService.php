<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use EffectConnect\Marketplaces\Exception\OrderImportFailedException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\Marketplaces\Service\Transformer\OrderTransformerService;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use EffectConnect\PHPSdk\Core;
use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\PHPSdk\Core\Exception\InvalidPropertyValueException;
use EffectConnect\PHPSdk\Core\Exception\MissingFilterValueException;
use EffectConnect\PHPSdk\Core\Model\Filter\HasStatusFilter;
use EffectConnect\PHPSdk\Core\Model\Filter\HasTagFilter;
use EffectConnect\PHPSdk\Core\Model\Filter\TagFilterValue;
use EffectConnect\PHPSdk\Core\Model\Response\Order;
use EffectConnect\PHPSdk\Core\Model\Request\OrderList;
use Exception;
use Monolog\Logger;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class OrderImportService
 * @package EffectConnect\Marketplaces\Service\Api
 */
class OrderImportService extends AbstractOrderService
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS      = LoggerProcess::IMPORT_ORDERS;

    /**
     * @var OrderTransformerService
     */
    protected $_orderTransformerService;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * OrderImportService constructor.
     *
     * @param InteractionService $interactionService
     * @param OrderTransformerService $orderTransformerService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        InteractionService $interactionService,
        OrderTransformerService $orderTransformerService,
        LoggerFactory $loggerFactory
    ) {
        parent::__construct($interactionService, $loggerFactory);

        $this->_orderTransformerService     = $orderTransformerService;
        $this->_logger                      = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * Import all open orders.
     *
     * @param SalesChannelEntity $salesChannel
     * @param bool $externallyFulfilledOrders
     * @return void
     * @throws OrderImportFailedException
     */
    public function importOrders(SalesChannelEntity $salesChannel, bool $externallyFulfilledOrders)
    {
        $this->_logger->info('Import orders for sales channel started.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ]
        ]);

        try {
            $core = $this->_interactionService->getInitializedSdk($salesChannel->getId());
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

            throw new OrderImportFailedException($salesChannel->getId());
        }

        $orders = $this->getAllOrders($core, $salesChannel, $externallyFulfilledOrders);

        try {
            $this->_orderTransformerService->createOrdersForSalesChannel($salesChannel, $orders);
        } catch (Exception $e) {
            $this->_logger->error('Failed to create order (or finding the sales channel).', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            $this->_logger->info('Import orders for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            throw new OrderImportFailedException($salesChannel->getId());
        }

        $this->_logger->info('Import orders for sales channel ended.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ]
        ]);
    }

    protected function getAllOrders(Core $core, SalesChannelEntity $salesChannel, bool $externallyFulfilled): array
    {
        try {
            return $this->orderListReadCall($core, $externallyFulfilled);
        } catch (Exception $e) {
            $this->_logger->error('Order List Read API call failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);

            $this->_logger->info('Import orders for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ]
            ]);
            throw new OrderImportFailedException($salesChannel->getId());
        }
    }

    /**
     * Create order list read call.
     *
     * @param Core $core
     * @param bool $externallyFulfilled
     * @return Order[]
     * @throws ApiCallFailedException
     * @throws InvalidPropertyValueException
     * @throws MissingFilterValueException
     */
    protected function orderListReadCall(Core $core, bool $externallyFulfilled): array
    {
        $orderListCall  = $core->OrderListCall();
        $orderList      = new OrderList();

        $this->addFilters($orderList, $externallyFulfilled);

        $apiCall = $orderListCall->read($orderList);

        $apiCall->setTimeout(30000);

        $apiCall->call();

        $result = $this->_interactionService->resolveResponse($apiCall);

        return $result->getOrders();
    }

    /**
     * @param OrderList $orderList
     * @param bool $externallyFulfilled
     * @throws InvalidPropertyValueException
     * @throws MissingFilterValueException
     */
    protected function addFilters(OrderList &$orderList, bool $externallyFulfilled)
    {
        $this->addStatusFilters($orderList, $externallyFulfilled);
        $this->addTagFilters($orderList, $externallyFulfilled);
    }

    /**
     * Add status filters.
     *
     * @param OrderList $orderList
     * @param bool $externallyFulfilled
     * @throws InvalidPropertyValueException
     * @throws MissingFilterValueException
     */
    protected function addStatusFilters(OrderList &$orderList, bool $externallyFulfilled)
    {
        $statusFilters = $externallyFulfilled ? self::EXTERNAL_STATUS_FILTERS : self::INTERNAL_STATUS_FILTERS;
        $orderList->addFilter((new HasStatusFilter())->setFilterValue($statusFilters));
    }

    /**
     * Add exclude tag filters.
     *
     * @param OrderList $orderList
     * @param bool $externallyFulfilled
     * @throws InvalidPropertyValueException
     * @throws MissingFilterValueException
     */
    protected function addTagFilters(OrderList &$orderList, bool $externallyFulfilled)
    {
        $hasTagFilter = new HasTagFilter();

        foreach (static::EXCLUDE_TAG_FILTERS as $excludeTag) {
            $hasTagFilter->setFilterValue((new TagFilterValue())
                ->setExclude(true)
                ->setTagName($excludeTag));
        }

        if ($externallyFulfilled) {
            $hasTagFilter->setFilterValue((new TagFilterValue())
                ->setExclude(false)
                ->setTagName(self::ORDER_EXTERNAL_FULFILMENT_TAG));
        }

        $orderList->addFilter($hasTagFilter);
    }
}