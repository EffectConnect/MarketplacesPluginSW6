<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Handler;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class EffectConnectPayment
 * @package EffectConnect\Marketplaces\Handler
 */
class EffectConnectPayment implements SynchronousPaymentHandlerInterface
{
    /**
     * The name for the EffectConnect Payment.
     */
    public const PAYMENT_METHOD_NAME        = 'EffectConnect Payment';

    /**
     * The description for the EffectConnect Payment.
     */
    public const PAYMENT_METHOD_DESCRIPTION = 'EffectConnect Payment';

    /**
     * @var OrderTransactionStateHandler
     */
    protected $_transactionStateHandler;

    /**
     * EffectConnectPayment constructor.
     *
     * @param OrderTransactionStateHandler $transactionStateHandler
     */
    public function __construct(OrderTransactionStateHandler $transactionStateHandler)
    {
        $this->_transactionStateHandler = $transactionStateHandler;
    }

    /**
     * @inheritDoc
     * @param SyncPaymentTransactionStruct $transaction
     * @param RequestDataBag $dataBag
     * @param SalesChannelContext $salesChannelContext
     */
    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        $this->_transactionStateHandler->process($transaction->getOrderTransaction()->getId(), $salesChannelContext->getContext());
    }
}