<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Transformer;

use DateTime;
use EffectConnect\Marketplaces\Exception\ProductNotFoundException;
use EffectConnect\Marketplaces\Helper\SystemHelper;
use EffectConnect\Marketplaces\Service\CustomFieldService;
use EffectConnect\PHPSdk\Core\Model\Response\Line;
use EffectConnect\PHPSdk\Core\Model\Response\LineProductIdentifiers;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OrderLineTransformerService
 * @package EffectConnect\Marketplaces\Service\Transformer
 */
class OrderLineTransformerService
{
    /**
     * @var EntityRepository
     */
    protected $_productRepository;

    /**
     * @var QuantityPriceCalculator
     */
    protected $_quantityPriceCalculator;

    /**
     * @var ContainerInterface
     */
    protected $_container;

    /**
     * OrderLineTransformerService constructor.
     *
     * @param EntityRepository $productRepository
     * @param QuantityPriceCalculator $quantityPriceCalculator
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityRepository $productRepository,
        QuantityPriceCalculator $quantityPriceCalculator,
        ContainerInterface $container
    ) {
        $this->_productRepository       = $productRepository;
        $this->_quantityPriceCalculator = $quantityPriceCalculator;
        $this->_container               = $container;
    }

    /**
     * Transform the order line.
     *
     * @param Line $line
     * @param int $index
     * @param SalesChannelContext $context
     * @return array
     * @throws ProductNotFoundException
     */
    public function transformOrderLine(Line $line, int $index, SalesChannelContext $context): array
    {
        $product            = $this->getProduct($line->getProduct(), $context);
        $priceDefinition    = $this->getPriceDefinition($line->getLineAmount(), $product, $context);
        $price              = $this->_quantityPriceCalculator->calculate($priceDefinition, $context);
        $label              = !empty($product->getName()) ? $product->getName() : $line->getProductTitle();
        $lineId             = Uuid::randomHex();
        $isNew              = (($product->has('isNew') && is_bool($product->get('isNew'))) ? boolval($product->get('isNew')) : false);

        $customFields               = [
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_EFFECTCONNECT_LINE_ID  => $line->getIdentifiers()->getEffectConnectLineId(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_CHANNEL_LINE_ID        => $line->getIdentifiers()->getChannelLineId(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_EFFECTCONNECT_ID       => intval($line->getIdentifiers()->getEffectConnectId()),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_CONNECTION_LINE_ID     => $line->getIdentifiers()->getConnectionLineId(),
            CustomFieldService::CUSTOM_FIELD_KEY_ORDER_LINE_ITEM_PRODUCT_ID             => $line->getProductId()
        ];

        $customFields[CustomFieldService::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES]                   = $customFields;
        $customFields[CustomFieldService::CUSTOM_FIELDSET_KEY_EFFECTCONNECT_MARKETPLACES_ORDER_LINE_ITEM]   = $customFields;

        $transformedOrderLine = [
            'id'                => $lineId,
            'identifier'        => $product->getId(),
            'referencedId'      => $product->getId(),
            'productId'         => $product->getId(),
            'quantity'          => 1,
            'unitPrice'         => $line->getLineAmount(),
            'totalPrice'        => $line->getLineAmount(),
            'label'             => !empty($label) ? $label : '-',
            'description'       => $line->getProductTitle(),
            'good'              => true,
            'removable'         => true,
            // 'coverId'           => $product->getCoverId(), // TODO: Causes an error in some cases (Cannot add or update a child row: a foreign key constraint fails - (`order_line_item`, CONSTRAINT `fk.order_line_item.cover_id` FOREIGN KEY (`cover_id`) REFERENCES `media` (`id`) ON UPDATE CASCADE)\"))
            'stackable'         => true,
            'position'          => $index,
            'price'             => $price,
            'priceDefinition'   => $priceDefinition,
            'type'              => LineItem::PRODUCT_LINE_ITEM_TYPE,
            'customFields'      => $customFields,
            'payload'           => [
                'isNew'             => $isNew,
                'taxId'             => $product->getTaxId(),
                'tagIds'            => $product->getTagIds(),
                'options'           => array_values(
                    array_map(function (PropertyGroupOptionEntity $option) {
                        return [
                            'group'     => $option->getGroup()->getName(),
                            'option'    => $option->getName(),
                        ];
                    }, (!is_null($product->getOptions()) ? $product->getOptions()->getElements() : []))
                ),
                'createdAt'         => $product->getCreatedAt()->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                'optionIds'         => $product->getOptionIds(),
                'isCloseout'        => $product->getIsCloseout(),
                'categoryIds'       => $product->getCategoryTree(),
                'propertyIds'       => $product->getPropertyIds(),
                'releaseDate'       => ($product->getReleaseDate() ?? new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                'customFields'      => $product->getCustomFields(),
                'productNumber'     => $product->getProductNumber(),
                'manufacturerId'    => $product->getManufacturerId(),
                'markAsTopseller'   => $product->getMarkAsTopseller()
            ]
        ];

        // PurchasePrice changed to PurchasePrices in Shopware 6.4.
        // The if-statement below is to support backwards compatibility.
        if (SystemHelper::compareVersion('6.4', '<'))
        {
            // Shopware 6.3 and lower.

            $transformedOrderLine['purchasePrice'] = $product->getPurchasePrice();
        } else {
            // Shopware 6.4 and higher.

            $transformedOrderLine['purchasePrices'] = $product->getPurchasePrices();
        }

        return $transformedOrderLine;
    }

    /**
     * Transform transaction fee to order line.
     *
     * @param QuantityPriceDefinition $definition
     * @param CalculatedPrice $price
     * @param int $index
     * @return array|null
     */
    public function transformTransactionFeeOrderLine(QuantityPriceDefinition $definition, CalculatedPrice $price, int $index): ?array
    {
        if ($price->getTotalPrice() <= 0) {
            return null;
        }

        $lineId             = Uuid::randomHex();

        return [
            'id'                => $lineId,
            'identifier'        => $lineId,
            'referencedId'      => $lineId,
            'quantity'          => 1,
            'unitPrice'         => $price->getUnitPrice(),
            'totalPrice'        => $price->getTotalPrice(),
            'label'             => 'Transaction Fee',
            'description'       => 'Transaction Fee',
            'good'              => true,
            'removable'         => true,
            'stackable'         => true,
            'position'          => $index,
            'price'             => $price,
            'priceDefinition'   => $definition,
            'type'              => LineItem::CUSTOM_LINE_ITEM_TYPE
        ];
    }

    /**
     * Get the product for the order line.
     *
     * @param LineProductIdentifiers $productIdentifiers
     * @param SalesChannelContext $context
     * @return ProductEntity
     * @throws ProductNotFoundException
     */
    protected function getProduct(LineProductIdentifiers $productIdentifiers, SalesChannelContext $context): ProductEntity
    {
        $criteria   = new Criteria();
        $filter     = new EqualsFilter('id', $productIdentifiers->getIdentifier());

        $criteria
            ->addFilter($filter)
            ->addAssociation('options')
            ->addAssociation('options.group');

        $products   = $this->_productRepository->search($criteria, $context->getContext());

        // Product not found.
        if ($products->getTotal() === 0) {
            throw new ProductNotFoundException(
                $productIdentifiers->getIdentifier(),
                $productIdentifiers->getEan(),
                $productIdentifiers->getSku()
            );
        }

        return $products->first();
    }

    /**
     * Get the total costs for the order line.
     *
     * @param float $amount
     * @return CalculatedPrice
     */
    protected function getPrice(float $amount): CalculatedPrice
    {
        return new CalculatedPrice(
            $amount,
            $amount,
            new CalculatedTaxCollection(),
            new TaxRuleCollection()
        );
    }

    /**
     * Get the quantity price definition for the order line.
     *
     * @param float $amount
     * @param ProductEntity $product
     * @param SalesChannelContext $context
     * @return QuantityPriceDefinition
     */
    protected function getPriceDefinition(float $amount, ProductEntity $product, SalesChannelContext $context): QuantityPriceDefinition
    {
        // QuantityPriceDefinition constructor changed in Shopware 6.4.
        // The if-statement below is to support backwards compatibility.
        if (SystemHelper::compareVersion('6.4', '<')) {
            // Shopware 6.3 and lower.

            return new QuantityPriceDefinition(
                $amount,
                $context->buildTaxRules($product->getTaxId()),
                2,
                1,
                true
            );
        } else {
            // Shopware 6.4 and higher.

            $definition = new QuantityPriceDefinition($amount, $context->buildTaxRules($product->getTaxId()), 1);

            $definition->setIsCalculated(true);

            return $definition;
        }
    }
}