<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Traits;

use EffectConnect\Marketplaces\Handler\EffectConnectShipment;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\DeliveryTime\DeliveryTimeEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Trait ShippingMethodTrait
 * @package EffectConnect\Marketplaces\Traits
 */
trait ShippingMethodTrait
{
    /**
     * Add the EffectConnect Shipment method.
     *
     * @param Context $context
     * @param ContainerInterface $container
     */
    protected function addShipmentMethod(Context $context, ContainerInterface $container): void
    {
        $shipmentMethodId       = $this->getShipmentMethodId($container);

        // Shipment method exists already.
        if (!is_null($shipmentMethodId)) {
            return;
        }

        $availabilityRuleId     = $this->getAvailabilityRuleId($context, $container);
        $deliveryTimeId         = $this->getDeliveryTimeId($context, $container);

        // Availability rule or delivery time do exists already.
        if (is_null($availabilityRuleId) || is_null($deliveryTimeId)) {
            return;
        }

        $shipmentData           = [
            'name'                  => EffectConnectShipment::SHIPPING_METHOD_NAME,
            'entityName'            => EffectConnectShipment::SHIPPING_METHOD_NAME,
            'technicalName'         => EffectConnectShipment::SHIPPING_METHOD_TECHNICAL_NAME,
            'description'           => EffectConnectShipment::SHIPPING_METHOD_DESCRIPTION,
            'availabilityRuleId'    => $availabilityRuleId,
            'deliveryTimeId'        => $deliveryTimeId
        ];

        /**
         * @var EntityRepository $shipmentRepository
         */
        $shipmentRepository     = $container->get('shipping_method.repository');

        $shipmentRepository->create([$shipmentData], $context);
    }

    /**
     * Activate or deactivate the EffectConnect Shipment method.
     *
     * @param bool $active
     * @param Context $context
     * @param ContainerInterface $container
     */
    protected function setShipmentMethodIsActive(bool $active, Context $context, ContainerInterface $container): void
    {
        /**
         * @var EntityRepository $shipmentRepository
         */
        $shipmentRepository = $container->get('shipping_method.repository');
        $shipmentMethodId   = $this->getShipmentMethodId($container);

        // Shipment method does not exists.
        if (!$shipmentMethodId) {
            return;
        }

        $shipmentMethod     = [
            'id'        => $shipmentMethodId,
            'active'    => $active,
        ];

        $shipmentRepository->update([$shipmentMethod], $context);
    }

    /**
     * Get the EffectConnect Shipment method ID.
     *
     * @param ContainerInterface $container
     * @return string|null
     */
    protected function getShipmentMethodId(ContainerInterface $container): ?string
    {
        /**
         * @var EntityRepository $shipmentRepository
         */
        $shipmentRepository = $container->get('shipping_method.repository');
        $criteria           = new Criteria();
        $filter             = new EqualsFilter('name', EffectConnectShipment::SHIPPING_METHOD_NAME);

        $criteria->addFilter($filter);

        $shipmentIds        = $shipmentRepository->searchIds($criteria, Context::createDefaultContext());

        // Shipment not found.
        if ($shipmentIds->getTotal() === 0) {
            return null;
        }

        return $shipmentIds->getIds()[0];
    }

    /**
     * Get the EffectConnect Shipment Availability Rule ID.
     *
     * @param Context $context
     * @param ContainerInterface $container
     * @param bool $created
     * @return string|null
     */
    protected function getAvailabilityRuleId(Context $context, ContainerInterface $container, bool $created = false): ?string
    {
        /**
         * @var EntityRepository $ruleRepository
         */
        $ruleRepository = $container->get('rule.repository');
        $criteria       = new Criteria();
        $filter         = new EqualsFilter('name', EffectConnectShipment::SHIPPING_METHOD_AVAILABILITY_RULE_NAME);

        $criteria->addFilter($filter);

        $ruleIds        = $ruleRepository->searchIds($criteria, Context::createDefaultContext());

        // Availability rule not found.
        if ($ruleIds->getTotal() === 0) {
            if (!$created) {
                return $this->addAvailabilityRule($context, $container);
            }

            return null;
        }

        return $ruleIds->getIds()[0];
    }

    /**
     * Add the EffectConnect Shipment Availability Rule.
     *
     * @param Context $context
     * @param ContainerInterface $container
     * @return string|null
     */
    protected function addAvailabilityRule(Context $context, ContainerInterface $container): ?string
    {
        $ruleData       = [
            'name'          => EffectConnectShipment::SHIPPING_METHOD_AVAILABILITY_RULE_NAME,
            'description'   => EffectConnectShipment::SHIPPING_METHOD_AVAILABILITY_RULE_DESCRIPTION,
            'priority'      => 999
        ];

        /**
         * @var EntityRepository $ruleRepository
         */
        $ruleRepository = $container->get('rule.repository');

        $ruleRepository->create([$ruleData], $context);

        return $this->getAvailabilityRuleId($context, $container, true);
    }

    /**
     * Get the EffectConnect Shipment Delivery Time ID.
     *
     * @param Context $context
     * @param ContainerInterface $container
     * @param bool $created
     * @return string|null
     */
    protected function getDeliveryTimeId(Context $context, ContainerInterface $container, bool $created = false): ?string
    {
        /**
         * @var EntityRepository $deliveryRepository
         */
        $deliveryRepository = $container->get('delivery_time.repository');
        $criteria           = new Criteria();
        $filter             = new EqualsFilter('name', EffectConnectShipment::SHIPPING_METHOD_DELIVERY_TIME_NAME);

        $criteria->addFilter($filter);

        $deliveryIds        = $deliveryRepository->searchIds($criteria, Context::createDefaultContext());

        // Delivery time not found.
        if ($deliveryIds->getTotal() === 0) {
            if (!$created) {
                return $this->addDeliveryTime($context, $container);
            }

            return null;
        }

        return $deliveryIds->getIds()[0];
    }

    /**
     * Add the EffectConnect Shipment Delivery Time.
     *
     * @param Context $context
     * @param ContainerInterface $container
     * @return string|null
     */
    protected function addDeliveryTime(Context $context, ContainerInterface $container): ?string
    {
        $deliveryData       = [
            'name'          => EffectConnectShipment::SHIPPING_METHOD_DELIVERY_TIME_NAME,
            'min'           => 1,
            'max'           => 5,
            'unit'          => DeliveryTimeEntity::DELIVERY_TIME_DAY
        ];

        /**
         * @var EntityRepository $deliveryRepository
         */
        $deliveryRepository = $container->get('delivery_time.repository');

        $deliveryRepository->create([$deliveryData], $context);

        return $this->getDeliveryTimeId($context, $container, true);
    }
}