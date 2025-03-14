<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Subscriber;

use EffectConnect\Marketplaces\Core\ExportQueue\Data\OrderExportQueueData;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueEntity;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueStatus;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueType;
use EffectConnect\Marketplaces\Enum\FulfilmentType;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Object\OrderLineDeliveryData;
use EffectConnect\Marketplaces\Service\CustomFieldService;
use EffectConnect\Marketplaces\Service\ExportQueueService;
use Monolog\Logger;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
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
     * @var EntityRepository
     */
    protected $_orderDeliveryRepository;

    /**
     * @var LoggerFactory
     */
    protected $_loggerFactory;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var ExportQueueService
     */
    private $_exportQueueService;

    /**
     * OrderShippedSubscriber constructor.
     *
     * @param EntityRepository $orderDeliveryRepository
     * @param ExportQueueService $exportQueueService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        EntityRepository $orderDeliveryRepository,
        ExportQueueService $exportQueueService,
        LoggerFactory $loggerFactory
    ) {
        $this->_orderDeliveryRepository = $orderDeliveryRepository;
        $this->_exportQueueService      = $exportQueueService;
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
        if ($delivery->getOrder()->getOrderDateTime()->diff(new \DateTime())->days > 28) {
            // Prevents order export batches from failing due to outdated orders.
            // Orders cannot be updated after 30 days.
            return 'order is too old.';
        }
        return null;
    }

    /**
     * Is triggered when an order delivery is set.
     *
     * @param EntityWrittenEvent $event
     */
    public function onOrderDeliveryWritten(EntityWrittenEvent $event)
    {
        $this->_logger->info('Export shipment event subscriber for sales channel started.', [
            'process'       => static::LOGGER_PROCESS
        ]);

        foreach ($event->getWriteResults() as $writeResult) {

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

            $queueData = (new OrderExportQueueData())->setLineDeliveries($lineDeliveries);
            $queue = (new ExportQueueEntity())
                ->setIdentifier($delivery->getId())
                ->setSalesChannelId($salesChannel->getId())
                ->setData($queueData->toArray())
                ->setType(ExportQueueType::SHIPMENT)
                ->setStatus(ExportQueueStatus::QUEUED)
            ;
            $this->_exportQueueService->create($queue);

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