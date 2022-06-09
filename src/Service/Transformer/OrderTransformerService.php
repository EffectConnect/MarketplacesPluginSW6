<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Transformer;

use DateTime;
use EffectConnect\Marketplaces\Enum\FulfilmentType;
use EffectConnect\Marketplaces\Exception\CountryNotFoundException;
use EffectConnect\Marketplaces\Exception\CountryStateNotFoundException;
use EffectConnect\Marketplaces\Exception\CreateCurrencyFailedException;
use EffectConnect\Marketplaces\Exception\CreateSalutationFailedException;
use EffectConnect\Marketplaces\Exception\CreatingDeliveryDateFailedException;
use EffectConnect\Marketplaces\Exception\CreatingOrderFailedException;
use EffectConnect\Marketplaces\Exception\InvalidAddressTypeException;
use EffectConnect\Marketplaces\Exception\NoPaymentMethodFoundException;
use EffectConnect\Marketplaces\Exception\NoShippingMethodFoundException;
use EffectConnect\Marketplaces\Exception\ObtainingStateFailedException;
use EffectConnect\Marketplaces\Exception\ProductNotFoundException;
use EffectConnect\Marketplaces\Exception\SalesChannelNotFoundException;
use EffectConnect\Marketplaces\Exception\UpdatingOrderNumberFailedException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Handler\EffectConnectPayment;
use EffectConnect\Marketplaces\Handler\EffectConnectShipment;
use EffectConnect\Marketplaces\Helper\StateHelper;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Object\OrderImportResult;
use EffectConnect\Marketplaces\Service\Api\AbstractOrderService;
use EffectConnect\Marketplaces\Service\Api\OrderUpdateService;
use EffectConnect\Marketplaces\Service\CustomFieldService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use EffectConnect\PHPSdk\Core\Model\Request\OrderFee;
use EffectConnect\PHPSdk\Core\Model\Response\Order;
use Exception;
use Monolog\Logger;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryDate;
use Shopware\Core\Checkout\Cart\Price\AmountCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\StateMachine\StateMachineRegistry;

/**
 * Class OrderTransformerService
 * @package EffectConnect\Marketplaces\Service\Transformer
 */
class OrderTransformerService
{
    /**
     * The logger process for this transformer.
     */
    protected const LOGGER_PROCESS = LoggerProcess::IMPORT_ORDERS;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var SalesChannelService
     */
    protected $_salesChannelService;

    /**
     * @var StateMachineRegistry
     */
    protected $_stateMachineRegistry;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_currencyRepository;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_paymentRepository;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_shipmentRepository;

    /**
     * @var CustomerTransformerService
     */
    protected $_customerTransformerService;

    /**
     * @var OrderLineTransformerService
     */
    protected $_orderLineTransformerService;

    /**
     * @var NumberRangeValueGeneratorInterface
     */
    protected $_numberRangeValueGenerator;

    /**
     * @var OrderUpdateService
     */
    protected $_orderUpdateService;

    /**
     * @var QuantityPriceCalculator
     */
    protected $_quantityPriceCalculator;

    /**
     * @var AmountCalculator
     */
    protected $_amountCalculator;

    /**
     * @var LoggerFactory
     */
    protected $_loggerFactory;

    /**
     * @var SettingsService
     */
    protected $_settingsService;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_orderTransactionRepository;

    /**
     * @var SalesChannelEntity
     */
    private $_salesChannel;

    /**
     * @var SalesChannelContext
     */
    private $_salesChannelContext;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var SettingStruct
     */
    protected $_settings;

    /**
     * OrderTransformerService constructor.
     *
     * @param EntityRepositoryInterface $orderRepository
     * @param SalesChannelService $salesChannelService
     * @param StateMachineRegistry $stateMachineRegistry
     * @param EntityRepositoryInterface $currencyRepository
     * @param EntityRepositoryInterface $paymentRepository
     * @param EntityRepositoryInterface $shipmentRepository
     * @param CustomerTransformerService $customerTransformerService
     * @param OrderLineTransformerService $orderLineTransformerService
     * @param NumberRangeValueGeneratorInterface $numberRangeValueGenerator
     * @param OrderUpdateService $orderUpdateService
     * @param QuantityPriceCalculator $quantityPriceCalculator
     * @param AmountCalculator $amountCalculator
     * @param LoggerFactory $loggerFactory
     * @param SettingsService $settingsService
     * @param EntityRepositoryInterface $orderTransactionRepository
     */
    public function __construct(
        EntityRepositoryInterface $orderRepository,
        SalesChannelService $salesChannelService,
        StateMachineRegistry $stateMachineRegistry,
        EntityRepositoryInterface $currencyRepository,
        EntityRepositoryInterface $paymentRepository,
        EntityRepositoryInterface $shipmentRepository,
        CustomerTransformerService $customerTransformerService,
        OrderLineTransformerService $orderLineTransformerService,
        NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        OrderUpdateService $orderUpdateService,
        QuantityPriceCalculator $quantityPriceCalculator,
        AmountCalculator $amountCalculator,
        LoggerFactory $loggerFactory,
        SettingsService $settingsService,
        EntityRepositoryInterface $orderTransactionRepository
    ) {
        $this->_orderRepository             = $orderRepository;
        $this->_salesChannelService         = $salesChannelService;
        $this->_stateMachineRegistry        = $stateMachineRegistry;
        $this->_currencyRepository          = $currencyRepository;
        $this->_paymentRepository           = $paymentRepository;
        $this->_shipmentRepository          = $shipmentRepository;
        $this->_customerTransformerService  = $customerTransformerService;
        $this->_orderLineTransformerService = $orderLineTransformerService;
        $this->_numberRangeValueGenerator   = $numberRangeValueGenerator;
        $this->_orderUpdateService          = $orderUpdateService;
        $this->_quantityPriceCalculator     = $quantityPriceCalculator;
        $this->_amountCalculator            = $amountCalculator;
        $this->_loggerFactory               = $loggerFactory;
        $this->_settingsService             = $settingsService;
        $this->_orderTransactionRepository  = $orderTransactionRepository;
        $this->_logger                      = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * @param SalesChannelEntity $salesChannelEntity
     * @param Order[] $orders
     * @throws SalesChannelNotFoundException
     */
    public function createOrdersForSalesChannel(SalesChannelEntity $salesChannelEntity, array $orders)
    {
        $this->_salesChannel        = $salesChannelEntity;
        $this->_salesChannelContext = $this->_salesChannelService->getSalesChannelContext($salesChannelEntity->getId());
        $this->_settings            = $this->_settingsService->getSettings($salesChannelEntity->getId());

        foreach ($orders as $order) {
            $effectConnectOrderNumber   = $order->getIdentifiers()->getEffectConnectNumber();

            if ($this->orderAlreadyExists($order)) {
                $this->_logger->info('Order import skipped.', [
                    'process'       => static::LOGGER_PROCESS,
                    'message'       => 'Order already imported.',
                    'sales_channel' => [
                        'id'    => $this->_salesChannel->getId(),
                        'name'  => $this->_salesChannel->getName(),
                    ],
                    'order'         => [
                        'effectconnect_order_number'    => $order->getIdentifiers()->getEffectConnectNumber(),
                        'channel_order_number'          => $order->getIdentifiers()->getChannelNumber()
                    ]
                ]);

                continue;
            }

            try {
                $orderData                  = $this->transformOrder($order);
                $orderNumber                = $this->saveOrder($orderData);
                $orderId                    = $orderNumber; // TODO: When the API supports strings as identifier, change to $orderData['id']).
                $orderData['orderNumber']   = $orderNumber;
                $orderImportResult          = new OrderImportResult($effectConnectOrderNumber, true, $orderId, $orderNumber);

                $this->_logger->info('Order import succeeded.', [
                    'process'       => static::LOGGER_PROCESS,
                    'sales_channel' => [
                        'id'    => $this->_salesChannel->getId(),
                        'name'  => $this->_salesChannel->getName(),
                    ],
                    'order'         => [
                        'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                        'channel_order_number'          => $order->getIdentifiers()->getChannelNumber(),
                        'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                        'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
                    ]
                ]);

                // TODO: Custom fields needs to be shown in the GUI.
            } catch (Exception $e) {
                $orderImportResult          = new OrderImportResult($effectConnectOrderNumber, false);

                $this->_logger->alert('Order import failed.', [
                    'process'       => static::LOGGER_PROCESS,
                    'message'       => $e->getMessage(),
                    'file'          => $e->getFile(),
                    'line'          => $e->getLine(),
                    'trace'         => $e->getTraceAsString(),
                    'sales_channel' => [
                        'id'    => $this->_salesChannel->getId(),
                        'name'  => $this->_salesChannel->getName(),
                    ],
                    'order'         => [
                        'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                        'channel_order_number'          => $order->getIdentifiers()->getChannelNumber(),
                        'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                        'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
                    ]
                ]);
            }

            try {
                $this->_orderUpdateService->updateOrder($this->_salesChannel, $orderImportResult);

                $this->_logger->info('Order update succeeded.', [
                    'process'       => LoggerProcess::UPDATE_ORDER,
                    'sales_channel' => [
                        'id'    => $this->_salesChannel->getId(),
                        'name'  => $this->_salesChannel->getName(),
                    ],
                    'order'         => [
                        'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                        'channel_order_number'          => $order->getIdentifiers()->getChannelNumber(),
                        'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                        'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
                    ]
                ]);
            } catch (Exception $e) {
                $this->_logger->alert('Order update failed.', [
                    'process'       => LoggerProcess::UPDATE_ORDER,
                    'message'       => $e->getMessage(),
                    'sales_channel' => [
                        'id'    => $this->_salesChannel->getId(),
                        'name'  => $this->_salesChannel->getName(),
                    ],
                    'order'         => [
                        'effectconnect_order_number'    => $orderImportResult->getEffectConnectOrderNumber(),
                        'channel_order_number'          => $order->getIdentifiers()->getChannelNumber(),
                        'shop_order_number'             => $orderImportResult->getShopwareOrderNumber(),
                        'shop_order_id'                 => $orderImportResult->getShopwareOrderId()
                    ]
                ]);
            }
        }
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function orderAlreadyExists(Order $order): bool
    {
        $criteria   = new Criteria();
        $filter     = new EqualsFilter('customFields.effectConnectOrderNumber', $order->getIdentifiers()->getEffectConnectNumber());

        $criteria->addFilter($filter);

        $orders     = $this->_orderRepository->search($criteria, $this->_salesChannelContext->getContext());

        return $orders->getTotal() > 0;
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function orderHasExternalFulfilmentTag(Order $order): bool
    {
        foreach($order->getTags() as $tag) {
            if ($tag->getTag() === AbstractOrderService::ORDER_EXTERNAL_FULFILMENT_TAG) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Order $order
     * @return array
     * @throws CountryNotFoundException
     * @throws CountryStateNotFoundException
     * @throws CreateCurrencyFailedException
     * @throws CreateSalutationFailedException
     * @throws CreatingDeliveryDateFailedException
     * @throws InvalidAddressTypeException
     * @throws NoPaymentMethodFoundException
     * @throws NoShippingMethodFoundException
     * @throws ObtainingStateFailedException
     * @throws ProductNotFoundException
     */
    public function transformOrder(Order $order): array
    {
        $orderId                    = Uuid::randomHex();
        $billingAddressId           = Uuid::randomHex();
        $shippingAddressId          = Uuid::randomHex();
        $orderLineIndex             = 1;
        $orderLines                 = [];
        $currency                   = $this->getCurrency($order->getCurrency());
        $billingAddress             = $this->_customerTransformerService->transformOrderAddress($billingAddressId, $order->getBillingAddress(), $this->_salesChannelContext);
        $shippingAddress            = $this->_customerTransformerService->transformOrderAddress($shippingAddressId, $order->getShippingAddress(), $this->_salesChannelContext);
        $orderCustomer              = $this->_customerTransformerService->transformOrderCustomer($order->getBillingAddress(), $this->_salesChannelContext);
        $paymentMethod              = $this->getPaymentMethod();
        $shippingMethod             = $this->getShippingMethod();
        $tags                       = [
            [ 'name'    => 'EffectConnect' ],
            [ 'name'    => $order->getChannelInfo()->getType() ],
            [ 'name'    => $order->getChannelInfo()->getSubtype() ],
            [ 'name'    => $order->getChannelInfo()->getTitle() ],
            [ 'name'    => $order->getIdentifiers()->getChannelNumber() ]
        ];

        $externallyFulfilled = $this->orderHasExternalFulfilmentTag($order);
        $stateMachine = $this->_stateMachineRegistry->getStateMachine(OrderStates::STATE_MACHINE, $this->_salesChannelContext->getContext());
        $orderStatusTechnicalName = $externallyFulfilled ? $this->_settings->getExternalOrderStatus() : $this->_settings->getOrderStatus();
        $stateId = StateHelper::getIdFromTechnicalName($stateMachine, $orderStatusTechnicalName);

        foreach ($order->getLines() as $line) {
            $orderLines[] = $this->_orderLineTransformerService->transformOrderLine($line, $orderLineIndex, $this->_salesChannelContext);
            $orderLineIndex++;
        }

        $transactionPriceDefinition = $this->getPriceDefinitionWithHighestOrderTaxRate($this->getTotalFee($order, OrderFee::FEE_TYPE_TRANSACTION), $orderLines);
        $transactionPrice           = $this->getPriceWithHighestOrderTaxRate($transactionPriceDefinition);
        $transactionLine            = $this->_orderLineTransformerService->transformTransactionFeeOrderLine($transactionPriceDefinition, $transactionPrice, $orderLineIndex);

        if (!is_null($transactionLine)) {
            $orderLines[] = $transactionLine;
        }

        $delivery                   = $this->transformDelivery($order, $shippingMethod, $shippingAddressId, $orderLines, $externallyFulfilled);
        $cartPrice                  = $this->getCartPrice($orderLines, $delivery['shippingCosts']);

        $customFields               = [
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_EFFECTCONNECT_ORDER_NUMBER => $order->getIdentifiers()->getEffectConnectNumber(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_CHANNEL_ORDER_NUMBER       => $order->getIdentifiers()->getChannelNumber(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_CHANNEL_ID                 => intval($order->getChannelInfo()->getId()),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_CHANNEL_TYPE               => $order->getChannelInfo()->getType(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_CHANNEL_SUBTYPE            => $order->getChannelInfo()->getSubtype(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_CHANNEL_TITLE              => $order->getChannelInfo()->getTitle(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_COMMISSION_FEE             => $this->getTotalFee($order, OrderFee::FEE_TYPE_COMMISSION),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_FULFILMENT_TYPE            => $externallyFulfilled ? FulfilmentType::EXTERNAL : FulfilmentType::INTERNAL,
        ];

        $customFields[CustomFieldService::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES]       = $customFields;
        $customFields[CustomFieldService::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER] = $customFields;

        $data               = [
            'id'                => $orderId,
            'currencyId'        => $currency->getId(),
            'currencyFactor'    => $currency->getFactor(),
            'salesChannelId'    => $this->_salesChannel->getId(),
            'billingAddressId'  => $billingAddressId,
            'orderDateTime'     => ($order->getDate())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'price'             => $cartPrice,
            'shippingCosts'     => $this->getShippingCosts($order, $orderLines),
            'orderCustomer'     => $orderCustomer,
            'languageId'        => $this->_salesChannel->getLanguageId(),
            'addresses'         => [
                $billingAddressId   => $billingAddress,
                $shippingAddressId  => $shippingAddress
            ],
            'deliveries'        => [
                $delivery
            ],
            'lineItems'         => $orderLines,
            'transactions'      => [
                $this->transformTransaction($cartPrice, $paymentMethod, $externallyFulfilled)
            ],
            'deepLinkCode'      => Random::getBase64UrlString(32),
            'stateId'           => $stateId,
            'customFields'      => $customFields,
            'documents'         => [],
            'tags'              => [],
            'affiliateCode'     => $order->getChannelInfo()->getType(),
            'campaignCode'      => null,
            'customerComment'   => null,
            'paymentMethodId'   => $paymentMethod->getId(),
            'item_rounding'     => [
                'decimals'          => 2,
                'interval'          => 0.01,
                'roundForNet'       => true,
            ],
            'total_rounding'    => [
                'decimals'          => 2,
                'interval'          => 0.01,
                'roundForNet'       => true,
            ]
        ];

        foreach ($order->getTags() as $tag) {
            $tags[] = [ 'name' => $tag->getTag() ];
        }

        foreach ($tags as $tag) {
            if (!empty($tag['name'])) {
                $data['tags'][] = $tag;
            }
        }

        return $data;
    }

    /**
     * Transform the transaction for the order.
     *
     * @param CartPrice $cartPrice
     * @param PaymentMethodEntity $paymentMethod
     * @param bool $externallyfulfilled
     * @return array
     * @throws ObtainingStateFailedException
     */
    public function transformTransaction(CartPrice $cartPrice, PaymentMethodEntity $paymentMethod, bool $externallyfulfilled): array
    {
        try {
            $stateMachine = $this->_stateMachineRegistry->getStateMachine(OrderTransactionStates::STATE_MACHINE, $this->_salesChannelContext->getContext());
        } catch (Exception $e) {
            throw new ObtainingStateFailedException(OrderTransactionStates::STATE_MACHINE);
        }

        $paymentStatusTechnicalName = $externallyfulfilled ? $this->_settings->getExternalPaymentStatus() : $this->_settings->getPaymentStatus();
        $stateId = StateHelper::getIdFromTechnicalName($stateMachine, $paymentStatusTechnicalName, OrderTransactionStates::STATE_PAID);

        return [
            'paymentMethodId'   => $paymentMethod->getId(),
            'amount'            => $this->getCalculatedPriceFromCartPrice($cartPrice),
            'stateId'           => $stateId
        ];
    }

    /**
     * Transform the delivery for the order.
     *
     * @param Order $order
     * @param ShippingMethodEntity $shippingMethod
     * @param string $shippingAddressId
     * @param array $orderLines
     * @return array
     * @throws CreatingDeliveryDateFailedException
     * @throws ObtainingStateFailedException
     */
    public function transformDelivery(Order $order, ShippingMethodEntity $shippingMethod, string $shippingAddressId, array $orderLines, bool $externallyFulfilled): array
    {
        try {
            $deliveryDate = new DeliveryDate(new DateTime('now'), (new DateTime('now'))->modify('+1 week'));
        } catch (Exception $e) {
            throw new CreatingDeliveryDateFailedException();
        }

        $technicalName = $externallyFulfilled ? $this->_settings->getExternalShippingStatus() : null;
        $stateMachine = $this->_stateMachineRegistry->getStateMachine(OrderDeliveryStates::STATE_MACHINE, $this->_salesChannelContext->getContext());
        $stateId = StateHelper::getIdFromTechnicalName($stateMachine, $technicalName, $externallyFulfilled ? OrderDeliveryStates::STATE_SHIPPED : null);

        return [
            'shippingOrderAddressId'    => $shippingAddressId,
            'shippingDateEarliest'      => $deliveryDate->getEarliest()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'shippingDateLatest'        => $deliveryDate->getLatest()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'shippingMethodId'          => $shippingMethod->getId(),
            'shippingCosts'             => $this->getShippingCosts($order, $orderLines),
            'stateId'                   => $stateId,
            'positions' => [],
        ];
    }

    /**
     * Save the order to the database.
     *
     * @param array $orderData
     * @return string
     * @throws CreatingOrderFailedException
     * @throws UpdatingOrderNumberFailedException
     */
    protected function saveOrder(array $orderData): string
    {
        $orderId        = $orderData['id'];

        try {
            $this->_salesChannelContext->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($orderData): void {
                $this->_orderRepository->create([$orderData], $context);
            });
        } catch (Exception $e) {
            throw new CreatingOrderFailedException($e->getMessage());
        }

        $criteria   = new Criteria();
        $filter     = new EqualsFilter('id', $orderId);

        $criteria->addFilter($filter);
        $criteria->getAssociation('transactions');

        $orders     = $this->_orderRepository->search($criteria, $this->_salesChannelContext->getContext());

        if ($orders->getTotal() !== 1) {
            throw new CreatingOrderFailedException(sprintf('%s orders found with order ID "%s"', $orders->getTotal(), $orderId));
        }

        try {
            $orderNumber = $this->generateOrderNumber();

            $this->_orderRepository->update([
                [
                    'id'            => $orderId,
                    'orderNumber'   => $orderNumber
                ]
            ], $this->_salesChannelContext->getContext());
        } catch (Exception $e) {
            throw new UpdatingOrderNumberFailedException($orderId);
        }

        /** @var OrderEntity $order */
        $order = $orders->first();

        $this->updateTransaction($order);

        return $orderNumber;
    }

    /**
     * Get the totals of a certain fee (OrderFee) from Order.
     *
     * @param Order $order
     * @param string $feeType
     * @return float
     */
    protected function getTotalFee(Order $order, string $feeType): float
    {
        $feeCosts = (float)0;

        foreach ($order->getFees() as $fee) {
            if ($fee->getType() === $feeType) {
                $feeCosts += $fee->getAmount();
            }
        }

        $feeCosts += $this->getTotalFeeOrderLines($order, $feeType);

        return $feeCosts;
    }

    /**
     * Get the totals of a certain fee (OrderFee) from OrderLines.
     *
     * @param Order $order
     * @param string $feeType
     * @return float
     */
    protected function getTotalFeeOrderLines(Order $order, string $feeType): float
    {
        $feeCosts = (float)0;

        foreach ($order->getLines() as $line) {
            foreach ($line->getFees() as $fee) {
                if ($fee->getType() === $feeType) {
                    $feeCosts += $fee->getAmount();
                }
            }
        }

        return $feeCosts;
    }

    /**
     * Get the cart price for the order.
     *
     * @param array $orderLines
     * @param CalculatedPrice $shippingPrice
     * @return CartPrice
     */
    protected function getCartPrice(array $orderLines, CalculatedPrice $shippingPrice): CartPrice
    {
        $orderLinePrices    = new PriceCollection(array_map(function (array $orderLine) {
            return $orderLine['price'];
        }, $orderLines));

        $shippingPrices     = new PriceCollection([
            $shippingPrice
        ]);

        return $this->_amountCalculator->calculate(
            $orderLinePrices,
            $shippingPrices,
            $this->_salesChannelContext
        );
    }

    /**
     * Get the price definition with the highest tax rate in the order.
     *
     * @param float $amount
     * @param array $orderLines
     * @return QuantityPriceDefinition
     */
    protected function getPriceDefinitionWithHighestOrderTaxRate(float $amount, array $orderLines): QuantityPriceDefinition
    {
        $highestTaxRule     = null;

        foreach ($orderLines as $orderLine) {
            /**
             * @var QuantityPriceDefinition $definition
             */
            $definition = $orderLine['priceDefinition'];

            foreach ($definition->getTaxRules() as $taxRule) {
                if (is_null($highestTaxRule)) {
                    $highestTaxRule = $taxRule;
                    continue;
                }

                if ($highestTaxRule->getTaxRate() < $taxRule->getTaxRate()) {
                    $highestTaxRule = $taxRule;
                }
            }
        }

        $taxRuleCollection  = new TaxRuleCollection();

        if (!is_null($highestTaxRule)) {
            $taxRuleCollection->add($highestTaxRule);
        }

        return new QuantityPriceDefinition(
            $amount,
            $taxRuleCollection,
            2,
            1,
            true
        );
    }

    /**
     * Get the calculated price with the highest tax rate in the order.
     *
     * @param QuantityPriceDefinition $taxRules
     * @return CalculatedPrice
     */
    protected function getPriceWithHighestOrderTaxRate(QuantityPriceDefinition $taxRules): CalculatedPrice
    {
        return $this->_quantityPriceCalculator->calculate($taxRules, $this->_salesChannelContext);
    }

    /**
     * Get the shipping costs for the order.
     *
     * @param Order $order
     * @param array $orderLines
     * @return CalculatedPrice
     */
    protected function getShippingCosts(Order $order, array $orderLines): CalculatedPrice
    {
        $shippingCosts      = $this->getTotalFee($order, OrderFee::FEE_TYPE_SHIPPING);
        $priceDefinition    = $this->getPriceDefinitionWithHighestOrderTaxRate($shippingCosts, $orderLines);
        $price              = $this->getPriceWithHighestOrderTaxRate($priceDefinition);

        return new CalculatedPrice(
            $shippingCosts,
            $shippingCosts,
            $price->getCalculatedTaxes(),
            $price->getTaxRules()
        );
    }

    /**
     * Get the total costs for the order.
     *
     * @param CartPrice $cartPrice
     * @return CalculatedPrice
     */
    protected function getCalculatedPriceFromCartPrice(CartPrice $cartPrice): CalculatedPrice
    {
        return new CalculatedPrice(
            $cartPrice->getTotalPrice(),
            $cartPrice->getTotalPrice(),
            $cartPrice->getCalculatedTaxes(),
            $cartPrice->getTaxRules()
        );
    }

    /**
     * Get the currency object for the order currency.
     *
     * @param string $currencyCode
     * @param bool $created
     * @return CurrencyEntity
     * @throws CreateCurrencyFailedException
     */
    protected function getCurrency(string $currencyCode, bool $created = false): CurrencyEntity
    {
        $criteria           = new Criteria();
        $isoCodeFilter      = new EqualsFilter('isoCode', $currencyCode);
        $shortNameFilter    = new EqualsFilter('shortName', $currencyCode);
        $multiFilter        = new MultiFilter(MultiFilter::CONNECTION_OR, [
            $isoCodeFilter,
            $shortNameFilter
        ]);

        $criteria->addFilter($multiFilter);

        $result = $this->_currencyRepository->search($criteria, $this->_salesChannelContext->getContext());

        if ($result->getTotal() > 0) {
            return $result->first();
        } elseif (!$created) {
            return $this->createCurrency($currencyCode);
        }

        throw new CreateCurrencyFailedException($currencyCode);
    }

    /**
     * Create a currency when it does not exists yet.
     *
     * @param string $currencyCode
     * @return CurrencyEntity
     * @throws CreateCurrencyFailedException
     */
    protected function createCurrency(string $currencyCode): CurrencyEntity
    {
        $this->_currencyRepository->create([
            [
                'isoCode'           => $currencyCode,
                'factor'            => 1.0,
                'symbol'            => $currencyCode,
                'shortName'         => $currencyCode,
                'name'              => $currencyCode,
                'decimalPrecision'  => 2
            ]
        ], $this->_salesChannelContext->getContext());

        return $this->getCurrency($currencyCode, true);
    }

    /**
     * Get the preferred Payment method.
     *
     * @return PaymentMethodEntity
     * @throws NoPaymentMethodFoundException
     */
    protected function getPaymentMethod(): PaymentMethodEntity
    {
        $paymentMethodId    = $this->_settings->getPaymentMethod();

        if (empty($paymentMethodId) || !Uuid::isValid($paymentMethodId)) {
            return $this->getEffectConnectPaymentMethod();
        }

        $criteria           = new Criteria();
        $filter             = new EqualsFilter('id', $paymentMethodId);

        $criteria->addFilter($filter);

        $payments           = $this->_paymentRepository->search($criteria, $this->_salesChannelContext->getContext());

        // Payment method not found.
        if ($payments->getTotal() === 0) {
            return $this->getEffectConnectPaymentMethod();
        }

        return $payments->first();
    }

    /**
     * Get the EffectConnect Payment method.
     *
     * @return PaymentMethodEntity
     * @throws NoPaymentMethodFoundException
     */
    protected function getEffectConnectPaymentMethod(): PaymentMethodEntity
    {
        $criteria           = new Criteria();
        $filter             = new EqualsFilter('handlerIdentifier', EffectConnectPayment::class);

        $criteria->addFilter($filter);

        $payments           = $this->_paymentRepository->search($criteria, $this->_salesChannelContext->getContext());

        // Payment method not found.
        if ($payments->getTotal() === 0) {
            return $this->getFirstActivePaymentMethod();
        }

        return $payments->first();
    }

    /**
     * Get the first active payment method (used when the EffectConnect Payment method is not found).
     *
     * @return PaymentMethodEntity
     * @throws NoPaymentMethodFoundException
     */
    protected function getFirstActivePaymentMethod(): PaymentMethodEntity
    {
        $criteria           = new Criteria();
        $filter             = new EqualsFilter('active', true);

        $criteria
            ->addFilter($filter)
            ->setLimit(1);

        $payments           = $this->_paymentRepository->search($criteria, $this->_salesChannelContext->getContext());

        // No active payment method found.
        if ($payments->getTotal() === 0) {
            throw new NoPaymentMethodFoundException();
        }

        return $payments->first();
    }

    /**
     * Get the preferred Shipment method.
     *
     * @return ShippingMethodEntity
     * @throws NoShippingMethodFoundException
     */
    protected function getShippingMethod(): ShippingMethodEntity
    {
        $shippingMethodId = $this->_settings->getShippingMethod();

        if (empty($shippingMethodId) || !Uuid::isValid($shippingMethodId)) {
            return $this->getEffectConnectShippingMethod();
        }

        $criteria           = new Criteria();
        $filter             = new EqualsFilter('id', $shippingMethodId);

        $criteria->addFilter($filter);

        $shipments          = $this->_shipmentRepository->search($criteria, $this->_salesChannelContext->getContext());

        // Shipping method not found.
        if ($shipments->getTotal() === 0) {
            return $this->getEffectConnectShippingMethod();
        }

        return $shipments->first();
    }

    /**
     * Get the EffectConnect Shipment method.
     *
     * @return ShippingMethodEntity
     * @throws NoShippingMethodFoundException
     */
    protected function getEffectConnectShippingMethod(): ShippingMethodEntity
    {
        $criteria           = new Criteria();
        $filter             = new EqualsFilter('name', EffectConnectShipment::SHIPPING_METHOD_NAME);

        $criteria->addFilter($filter);

        $shipments          = $this->_shipmentRepository->search($criteria, $this->_salesChannelContext->getContext());

        // Shipping method not found.
        if ($shipments->getTotal() === 0) {
            return $this->getFirstActiveShippingMethod();
        }

        return $shipments->first();
    }

    /**
     * Get the first active shipping method (used when the EffectConnect Shipment method is not found).
     *
     * @return ShippingMethodEntity
     * @throws NoShippingMethodFoundException
     */
    protected function getFirstActiveShippingMethod(): ShippingMethodEntity
    {
        $criteria           = new Criteria();
        $filter             = new EqualsFilter('active', true);

        $criteria
            ->addFilter($filter)
            ->setLimit(1);

        $shipments          = $this->_shipmentRepository->search($criteria, $this->_salesChannelContext->getContext());

        // No active payment method found.
        if ($shipments->getTotal() === 0) {
            throw new NoShippingMethodFoundException();
        }

        return $shipments->first();
    }

    /**
     * Generate an order number.
     *
     * @return string
     */
    protected function generateOrderNumber(): string
    {
        return $this->_numberRangeValueGenerator->getValue(
            OrderDefinition::ENTITY_NAME,
            $this->_salesChannelContext->getContext(),
            $this->_salesChannelContext->getSalesChannel()->getId()
        );
    }

    /**
     * Set updated at timestamps for the order transactions.
     *
     * @param OrderEntity $order
     */
    protected function updateTransaction(OrderEntity $order)
    {
        $transactions = ($order->getTransactions() ?? new OrderTransactionCollection());
        if ($transactions->count() > 0) {
            foreach ($transactions as $transaction) {
                try {
                    $transaction->setUpdatedAt(new DateTime());

                    $this->_orderTransactionRepository->update([
                        [
                            'id' => $transaction->getId(),
                            'updatedAt' => new DateTime()
                        ]
                    ], $this->_salesChannelContext->getContext());
                } catch (Exception $e) {
                    continue;
                }
            }
        }
    }
}