<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use EffectConnect\Marketplaces\Exception\CreatingOrderUpdateRequestFailedException;
use EffectConnect\Marketplaces\Exception\OrderUpdateFailedException;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Object\OrderImportResult;
use EffectConnect\PHPSdk\Core;
use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\PHPSdk\Core\Model\Request\OrderUpdate;
use EffectConnect\PHPSdk\Core\Model\Request\OrderUpdateRequest;
use Exception;
use Monolog\Logger;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class OrderUpdateService
 * @package EffectConnect\Marketplaces\Service\Api
 */
class OrderUpdateService extends AbstractOrderService
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::UPDATE_ORDER;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * Update a (succeeded/failed) imported order.
     *
     * @param SalesChannelEntity $salesChannel
     * @param OrderImportResult $orderImportResult
     * @return void
     * @throws OrderUpdateFailedException
     */
    public function updateOrder(SalesChannelEntity $salesChannel, OrderImportResult $orderImportResult)
    {
        $this->_logger = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);

        $this->_logger->info('Update order for sales channel started.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ],
            'order'         => [
                'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
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
                ],
                'order'         => [
                    'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                    'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                    'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
                ]
            ]);

            $this->_logger->info('Update order for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ],
                'order'         => [
                    'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                    'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                    'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
                ]
            ]);

            throw new OrderUpdateFailedException($salesChannel->getId());
        }

        try {
            $this->orderUpdateCall($core, $orderImportResult);
        } catch (Exception $e) {
            $this->_logger->error('Order Update API call failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ],
                'order'         => [
                    'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                    'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                    'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
                ]
            ]);

            $this->_logger->info('Import orders for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ],
                'order'         => [
                    'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                    'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                    'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
                ]
            ]);

            throw new OrderUpdateFailedException($salesChannel->getId());
        }

        $this->_logger->info('Update order succeeded.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ],
            'order'         => [
                'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
            ]
        ]);

        $this->_logger->info('Update order for sales channel ended.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ],
            'order'         => [
                'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
            ]
        ]);
    }

    /**
     * Create order update call.
     *
     * @param Core $core
     * @param OrderImportResult $orderImportResult
     * @return void
     * @throws ApiCallFailedException
     * @throws CreatingOrderUpdateRequestFailedException
     */
    protected function orderUpdateCall(Core $core, OrderImportResult $orderImportResult)
    {
        try {
            $orderCall = $core->OrderCall();
            $orderUpdate = new OrderUpdate();

            if ($orderImportResult->isImportSucceeded()) {
                $orderUpdate
                    ->setOrderIdentifierType(OrderUpdate::TYPE_EFFECTCONNECT_NUMBER)
                    ->setOrderIdentifier($orderImportResult->getEffectConnectOrderNumber())
                    ->setConnectionNumber($orderImportResult->getShopwareOrderNumber())
                    ->addTag(static::ORDER_IMPORT_SUCCEEDED_TAG);

                if (!empty($orderImportResult->getShopwareOrderId()) && is_numeric($orderImportResult->getShopwareOrderId())) {
                    $orderUpdate->setConnectionIdentifier(intval($orderImportResult->getShopwareOrderId()));
                }
            } else {
                $orderUpdate
                    ->setOrderIdentifierType(OrderUpdate::TYPE_EFFECTCONNECT_NUMBER)
                    ->setOrderIdentifier($orderImportResult->getEffectConnectOrderNumber())
                    ->addTag(static::ORDER_IMPORT_FAILED_TAG);
            }

            $orderRequest = new OrderUpdateRequest();

            $orderRequest->addOrderUpdate($orderUpdate);
        } catch (Exception $e) {
            throw new CreatingOrderUpdateRequestFailedException($orderImportResult->getEffectConnectOrderNumber(), $e->getMessage());
        }

        $apiCall = $orderCall->update($orderRequest);

        $apiCall->call();
        $this->_interactionService->resolveResponse($apiCall);
    }
}