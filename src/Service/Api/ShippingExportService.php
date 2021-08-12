<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use EffectConnect\Marketplaces\Exception\CreatingShippingExportRequestFailedException;
use EffectConnect\Marketplaces\Exception\ShipmentExportFailedException;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Object\OrderLineDeliveryData;
use EffectConnect\PHPSdk\Core;
use EffectConnect\Marketplaces\Exception\ApiCallFailedException;
use EffectConnect\PHPSdk\Core\Model\Request\OrderLineUpdate;
use EffectConnect\PHPSdk\Core\Model\Request\OrderUpdateRequest;
use Exception;
use Monolog\Logger;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class ShippingExportService
 * @package EffectConnect\Marketplaces\Service\Api
 */
class ShippingExportService extends AbstractOrderService
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::EXPORT_SHIPMENT;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @param SalesChannelEntity $salesChannel
     * @param OrderLineDeliveryData[] $deliveries
     * @throws ShipmentExportFailedException
     */
    public function exportShipment(SalesChannelEntity $salesChannel, array $deliveries)
    {
        $this->_logger = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);

        $deliveriesLoggerData = [];

        foreach ($deliveries as $delivery) {
            $deliveriesLoggerData[] = [
                'effectconnect_line_id' => $delivery->getEffectConnectLineId(),
                'effectconnect_id'      => $delivery->getEffectConnectId(),
                'tracking_number'       => $delivery->getTrackingNumber(),
                'carrier'               => $delivery->getCarrier()
            ];
        }

        $this->_logger->info('Export shipment for sales channel started.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ],
            'deliveries'    => $deliveriesLoggerData
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
                'deliveries'    => $deliveriesLoggerData
            ]);

            $this->_logger->info('Export shipment for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ],
                'deliveries'    => $deliveriesLoggerData
            ]);

            throw new ShipmentExportFailedException($salesChannel->getId());
        }

        try {
            $this->orderUpdateCall($core, $deliveries);
        } catch (Exception $e) {
            $this->_logger->error('Order Update API call failed.', [
                'process'       => static::LOGGER_PROCESS,
                'message'       => $e->getMessage(),
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ],
                'deliveries'    => $deliveriesLoggerData
            ]);

            $this->_logger->info('Export shipment for sales channel ended.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ],
                'deliveries'    => $deliveriesLoggerData
            ]);

            throw new ShipmentExportFailedException($salesChannel->getId());
        }

        $this->_logger->info('Export shipment succeeded.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ],
            'deliveries'    => $deliveriesLoggerData
        ]);

        $this->_logger->info('Export shipment for sales channel ended.', [
            'process'       => static::LOGGER_PROCESS,
            'sales_channel' => [
                'id'    => $salesChannel->getId(),
                'name'  => $salesChannel->getName(),
            ],
            'deliveries'    => $deliveriesLoggerData
        ]);
    }

    /**
     * Create order update call.
     *
     * @param Core $core
     * @param OrderLineDeliveryData[] $orderLineDeliveries
     * @return void
     * @throws ApiCallFailedException
     * @throws CreatingShippingExportRequestFailedException
     */
    protected function orderUpdateCall(Core $core, array $orderLineDeliveries)
    {
        $updatedOrderLines  = [];

        try {
            $orderCall      = $core->OrderCall();
            $orderRequest   = new OrderUpdateRequest();

            foreach ($orderLineDeliveries as $orderLineDeliveryData) {
                if (in_array(strval($orderLineDeliveryData->getEffectConnectLineId()), $updatedOrderLines)) {
                    continue;
                }

                $orderLineUpdate = new OrderLineUpdate();

                $orderLineUpdate->setOrderlineIdentifierType(OrderLineUpdate::TYPE_EFFECTCONNECT_LINE_ID);
                $orderLineUpdate->setOrderlineIdentifier(strval($orderLineDeliveryData->getEffectConnectLineId()));

                if (!is_null($orderLineDeliveryData->getTrackingNumber()) && !empty($orderLineDeliveryData->getTrackingNumber())) {
                    $orderLineUpdate->setTrackingNumber(strval($orderLineDeliveryData->getTrackingNumber()));
                }

                if (!is_null($orderLineDeliveryData->getCarrier()) && !empty($orderLineDeliveryData->getCarrier())) {
                    $orderLineUpdate->setCarrier(strval($orderLineDeliveryData->getCarrier()));
                }

                $orderRequest->addLineUpdate($orderLineUpdate);

                $updatedOrderLines[] = strval($orderLineDeliveryData->getEffectConnectLineId());
            }
        } catch (Exception $e) {
            throw new CreatingShippingExportRequestFailedException($e->getMessage());
        }

        $apiCall = $orderCall->update($orderRequest);

        $apiCall->call();
        $this->_interactionService->resolveResponse($apiCall);
    }
}