<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Subscriber;

use EffectConnect\Marketplaces\Enum\FulfilmentType;
use EffectConnect\Marketplaces\Exception\ShipmentExportFailedException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Object\OrderLineDeliveryData;
use EffectConnect\Marketplaces\Service\Api\ShippingExportService;
use EffectConnect\Marketplaces\Service\CustomFieldService;
use Monolog\Logger;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderShippedSubscriber
 * @package EffectConnect\Marketplaces\Subscriber
 */
class OrderShippedSubscriber implements EventSubscriberInterface
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::EXPORT_SHIPMENT;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_orderDeliveryRepository;

    /**
     * @var ShippingExportService
     */
    protected $_shippingExportService;

    /**
     * @var LoggerFactory
     */
    protected $_loggerFactory;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * OrderShippedSubscriber constructor.
     *
     * @param EntityRepositoryInterface $orderDeliveryRepository
     * @param ShippingExportService $shippingExportService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        EntityRepositoryInterface $orderDeliveryRepository,
        ShippingExportService $shippingExportService,
        LoggerFactory $loggerFactory
    ) {
        $this->_orderDeliveryRepository = $orderDeliveryRepository;
        $this->_shippingExportService   = $shippingExportService;
        $this->_loggerFactory           = $loggerFactory;
        $this->_logger                  = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::ORDER_DELIVERY_WRITTEN_EVENT => 'onOrderDeliveryWritten'
        ];
    }

    /**
     * @param OrderDeliveryEntity|null $delivery
     * @return string|null
     */
    private function getSkipReason(?OrderDeliveryEntity $delivery): ?string
    {
        if (is_null($delivery)) {
            return 'delivery does not exist';
        }
        if ($delivery->getStateMachineState()->getTechnicalName() !== OrderDeliveryStates::STATE_SHIPPED) {
            return 'delivery is not shipped yet.';
        }
        if (is_null($delivery->getOrder())) {
            return 'order does not exist.';
        }
        if (empty($delivery->getOrder()->getLineItems())) {
            return 'order has no orderlines.';
        }
        $customFields = $delivery->getOrder()->getCustomFields();
        if (empty($customFields[CustomFieldService::CUSTOM_FIELD_KEY_ORDER_EFFECTCONNECT_ORDER_NUMBER])) {
            return 'order is not an EffectConnect order.';
        }
        if ($customFields[CustomFieldService::CUSTOM_FIELD_KEY_ORDER_FULFILMENT_TYPE] === FulfilmentType::EXTERNAL) {
            return 'order is externally fulfilled.';
        }
        return null;
    }

    /**
     * Is triggered when an order delivery is set.
     *
     * @param EntityWrittenEvent $event
     * @throws ShipmentExportFailedException
     */
    public function onOrderDeliveryWritten(EntityWrittenEvent $event)
    {
        $this->_logger->info('Export shipment event subscriber for sales channel started.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        foreach ($event->getWriteResults() as $writeResult) {
            /**
             * @var OrderLineDeliveryData[] $lineDeliveries
             */
            $deliveryId     = is_array($writeResult->getPrimaryKey()) ? $writeResult->getPrimaryKey()[0] : $writeResult->getPrimaryKey();
            $delivery       = $this->getDeliveryById($deliveryId, $event->getContext());

            $skipReason = $this->getSkipReason($delivery);
            if ($skipReason !== null) {
                $this->_logger->notice('Delivery shipment skipped, reason: ' . $skipReason, [
                    'process'       => static::LOGGER_PROCESS,
                    'delivery'      => $deliveryId
                ]);
                continue;
            }

            $salesChannel = $delivery->getOrder()->getSalesChannel();

            $lineDeliveries = [];
            $trackingCode   = null;
            $carrier        = null;

            if (isset($delivery->getTrackingCodes()[0]) && !empty($delivery->getTrackingCodes()[0])) {
                $trackingCode   = $delivery->getTrackingCodes()[0];
            }

            if (!is_null($delivery->getShippingMethod())) {
                $carrier        = $delivery->getShippingMethod()->getName();
            }

            foreach ($delivery->getOrder()->getLineItems() as $lineItem) {
                $lineId = $lineItem->getCustomFields()['effectConnectLineId'] ?? null;
                if ($lineId) {
                    $effectConnectId = $lineItem->getCustomFields()['effectConnectId'] ?? null;
                    if ($effectConnectId) {
                        $effectConnectId = strval($effectConnectId);
                    }
                    $lineDeliveries[] = new OrderLineDeliveryData(strval($lineId), $effectConnectId, $trackingCode, $carrier);
                }
            }

            $this->_logger->notice('Delivery data obtained successfully.', [
                'process'       => static::LOGGER_PROCESS,
                'sales_channel' => [
                    'id'    => $salesChannel->getId(),
                    'name'  => $salesChannel->getName(),
                ],
                'delivery'      => $deliveryId,
                'order'         => [
                    'order_id'      => $delivery->getOrderId(),
                    'order_number'  => $delivery->getOrder()->getOrderNumber()
                ]
            ]);

            $this->_shippingExportService->exportShipment($salesChannel, $lineDeliveries);
        }

        $this->_logger->info('Export shipment event subscriber for sales channel ended.', [
            'process'       => static::LOGGER_PROCESS
        ]);
    }


    /**
     * Get an order delivery by id.
     *
     * @param $deliveryId
     * @param Context|null $context
     * @return OrderDeliveryEntity|null
     */
    protected function getDeliveryById($deliveryId, Context $context): ?OrderDeliveryEntity
    {
        $criteria = new Criteria([$deliveryId]);

        $criteria->addAssociations([
            'order',
            'order.lineItems',
            'order.salesChannel',
            'stateMachineState',
            'shippingMethod'
        ]);

        return $this->_orderDeliveryRepository
            ->search($criteria, $context)
            ->get($deliveryId);
    }
}