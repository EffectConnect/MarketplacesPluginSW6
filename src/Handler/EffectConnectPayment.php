<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Handler;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerType;
use Shopware\Core\Checkout\Payment\Cart\PaymentTransactionStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EffectConnectPayment
 * @package EffectConnect\Marketplaces\Handler
 */
class EffectConnectPayment extends AbstractPaymentHandler
{
    /**
     * The name for the EffectConnect Payment.
     */
    public const PAYMENT_METHOD_NAME        = 'EffectConnect Payment';

    /**
     * The technical name for the EffectConnect Payment.
     */
    public const PAYMENT_METHOD_TECHNICAL_NAME        = 'effectconnect_payment';

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

    public function supports(PaymentHandlerType $type, string $paymentMethodId, Context $context): bool
    {
        return true;
    }

    public function pay(Request $request, PaymentTransactionStruct $transaction, Context $context, ?Struct $validateStruct): ?RedirectResponse
    {
        $this->_transactionStateHandler->process($transaction->getOrderTransactionId(), $context);
        return null;
    }
}