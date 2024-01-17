<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Traits;

use EffectConnect\Marketplaces\Handler\EffectConnectPayment;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Trait PaymentMethodTrait
 * @package EffectConnect\Marketplaces\Traits
 */
trait PaymentMethodTrait
{
    /**
     * Add the EffectConnect Payment method.
     *
     * @param Context $context
     * @param ContainerInterface $container
     * @param string $pluginClass
     */
    protected function addPaymentMethod(Context $context, ContainerInterface $container, string $pluginClass): void
    {
        $paymentMethodId        = $this->getPaymentMethodId($container);

        // Payment method exists already.
        if (!is_null($paymentMethodId)) {
            return;
        }

        /**
         * @var PluginIdProvider $pluginIdProvider
         */
        $pluginIdProvider       = $container->get(PluginIdProvider::class);
        $pluginId               = $pluginIdProvider->getPluginIdByBaseClass($pluginClass, $context);
        $paymentData            = [
            'handlerIdentifier' => EffectConnectPayment::class,
            'name'              => EffectConnectPayment::PAYMENT_METHOD_NAME,
            'description'       => EffectConnectPayment::PAYMENT_METHOD_DESCRIPTION,
            'pluginId'          => $pluginId,
            'afterOrderEnabled' => false
        ];

        /**
         * @var EntityRepository $paymentRepository
         */
        $paymentRepository      = $container->get('payment_method.repository');

        $paymentRepository->create([$paymentData], $context);
    }

    /**
     * Activate or deactivate the EffectConnect Payment method.
     *
     * @param bool $active
     * @param Context $context
     * @param ContainerInterface $container
     */
    private function setPaymentMethodIsActive(bool $active, Context $context, ContainerInterface $container): void
    {
        /**
         * @var EntityRepository $paymentRepository
         */
        $paymentRepository  = $container->get('payment_method.repository');
        $paymentMethodId    = $this->getPaymentMethodId($container);

        // Payment method does not exists.
        if (is_null($paymentMethodId)) {
            return;
        }

        $paymentMethod = [
            'id'        => $paymentMethodId,
            'active'    => $active,
        ];

        $paymentRepository->update([$paymentMethod], $context);
    }

    /**
     * Get the EffectConnect Payment method ID.
     *
     * @param ContainerInterface $container
     * @return string|null
     */
    private function getPaymentMethodId(ContainerInterface $container): ?string
    {
        /**
         * @var EntityRepository $paymentRepository
         */
        $paymentRepository  = $container->get('payment_method.repository');
        $criteria           = new Criteria();
        $filter             = new EqualsFilter('handlerIdentifier', EffectConnectPayment::class);

        $criteria->addFilter($filter);

        $paymentIds         = $paymentRepository->searchIds($criteria, Context::createDefaultContext());

        // Payment not found.
        if ($paymentIds->getTotal() === 0) {
            return null;
        }

        return $paymentIds->getIds()[0];
    }
}