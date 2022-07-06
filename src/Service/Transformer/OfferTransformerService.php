<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Transformer;

use EffectConnect\Marketplaces\Exception\FileCreationFailedException;
use EffectConnect\Marketplaces\Exception\NoProductsFoundException;
use EffectConnect\Marketplaces\Exception\SalesChannelNotFoundException;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class OfferTransformerService
 * @package EffectConnect\Marketplaces\Service\Transformer
 */
class OfferTransformerService extends CatalogTransformerService
{
    /**
     * @inheritDoc
     */
    public const CONTENT_TYPE        = 'offer';

    /**
     * The logger process for this transformer.
     */
    protected const LOGGER_PROCESS      = LoggerProcess::EXPORT_OFFERS;

    /**
     * Build the offer XML for a specific sales channel.
     *
     * @param SalesChannelEntity $salesChannelEntity
     * @param array|null $productIds
     * @return string
     * @throws FileCreationFailedException
     * @throws SalesChannelNotFoundException
     */
    public function buildOfferXmlForSalesChannel(SalesChannelEntity $salesChannelEntity, ?array $productIds = null): string
    {
        return $this->buildCatalogXmlForSalesChannel($salesChannelEntity, $productIds);
    }

    /**
     * @inheritDoc
     */
    protected function getProductArray(ProductEntity $product): array
    {
        $productArray                   = [];
        $productArray['identifier']     = [ '_cdata' => strval($product->getId())];
        $productArray['options']        = [
            'option'    => $this->getProductOptionsArray($product)
        ];

        return $productArray;
    }

    /**
     * @inheritDoc
     */
    protected function getProductOptionArray(ProductEntity $product, ProductEntity $parent = null): array
    {
        $productOptionArray                             = [];
        $productOptionArray['identifier']               = [ '_cdata' => strval($product->getId())];
        $productOptionArray['cost']                     = $this->getProductCost($product);
        $productOptionArray['price']                    = $this->getProductSpecialPrice($product);
        $productOptionArray['priceOriginal']            = is_null($product->getPrice()) ? 0 : $product->getPrice()->first()->getGross();
        $productOptionArray['deliveryTime']             = [ '_cdata' => $this->getProductDeliveryTime($product)];
        $productOptionArray['stock']                    = $this->getProductStock($product);
        
        // Format number to a string with 2 decimals.
        if (is_float($productOptionArray['price'])) {
            $productOptionArray['price'] = number_format($productOptionArray['price'], 2, '.', '');
        }

        // Format number to a string with 2 decimals.
        if (is_float($productOptionArray['priceOriginal'])) {
            $productOptionArray['priceOriginal'] = number_format($productOptionArray['priceOriginal'], 2, '.', '');
        }

        // Format number to a string with 2 decimals.
        if (is_float($productOptionArray['cost'])) {
            $productOptionArray['cost'] = number_format($productOptionArray['cost'], 2, '.', '');
        }

        if ($productOptionArray['price'] === $productOptionArray['priceOriginal']) {
            unset($productOptionArray['priceOriginal']);
        }

        $this->removeFromArrayWhenEmpty($productOptionArray, [
            'cost',
            'price',
            'stock',
            'deliveryTime'
        ]);

        return $productOptionArray;
    }
}