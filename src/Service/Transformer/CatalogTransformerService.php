<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Transformer;

use DOMException;
use EffectConnect\Marketplaces\Exception\FileCreationFailedException;
use EffectConnect\Marketplaces\Exception\NoProductsFoundException;
use EffectConnect\Marketplaces\Exception\SalesChannelNotFoundException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Factory\SdkFactory;
use EffectConnect\Marketplaces\Helper\AttributesHelper;
use EffectConnect\Marketplaces\Helper\BarcodeValidator;
use EffectConnect\Marketplaces\Helper\FileHelper;
use EffectConnect\Marketplaces\Helper\SystemHelper;
use EffectConnect\Marketplaces\Helper\XmlHelper;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Object\LanguagesCollection;
use EffectConnect\Marketplaces\Service\LanguagesService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingsService;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use Exception;
use Monolog\Logger;
use Shopware\Core\Checkout\Cart\Rule\AlwaysValidRule;
use Shopware\Core\Content\Media\File\FileLoader;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionEntity;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\SalesChannelRepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\Tag\TagEntity;

/**
 * Class CatalogTransformerService
 * @package EffectConnect\Marketplaces\Service\Transformer
 */
class CatalogTransformerService
{
    /**
     * The directory where the catalog XML needs to be generated.
     */
    public const CONTENT_TYPE        = 'catalog';

    /**
     * The root element for the XML file containing the catalog.
     */
    protected const XML_ROOT_ELEMENT    = 'products';

    /**
     * The page size when iterating trough the products.
     */
    protected const PAGE_SIZE           = 10;

    /**
     * The logger process for this transformer.
     */
    protected const LOGGER_PROCESS      = LoggerProcess::EXPORT_CATALOG;

    /**
     * @var SdkFactory
     */
    protected $_sdkFactory;

    /**
     * @var SalesChannelRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var SalesChannelService
     */
    protected $_salesChannelService;

    /**
     * @var CategoryTransformerService
     */
    protected $_categoryTransformerService;

    /**
     * @var FileLoader
     */
    protected $_fileLoader;

    /**
     * @var LoggerFactory
     */
    protected $_loggerFactory;

    /**
     * @var SettingsService
     */
    protected $_settingsService;

    /**
     * @var LanguagesService
     */
    protected $_languagesService;

    /**
     * @var SeoUrlPlaceholderHandlerInterface
     */
    protected $_seoUrlService;

    /**
     * @var string
     */
    private $_fileLocation;

    /**
     * @var XmlHelper
     */
    private $_xmlHelper;

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
     * @var LanguagesCollection
     */
    protected $_languages;

    /**
     * @var AttributesHelper
     */
    protected $_attributesHelper;

    /**
     * CatalogTransformerService constructor.
     *
     * @param SdkFactory $sdkFactory
     * @param SalesChannelRepositoryInterface $productRepository
     * @param SalesChannelService $salesChannelService
     * @param CategoryTransformerService $categoryTransformerService
     * @param FileLoader $fileLoader
     * @param LoggerFactory $loggerFactory
     * @param SettingsService $settingsService
     * @param LanguagesService $languagesService
     * @param SeoUrlPlaceholderHandlerInterface $seoUrlService
     */
    public function __construct(
        SdkFactory $sdkFactory,
        SalesChannelRepositoryInterface $productRepository,
        SalesChannelService $salesChannelService,
        CategoryTransformerService $categoryTransformerService,
        FileLoader $fileLoader,
        LoggerFactory $loggerFactory,
        SettingsService $settingsService,
        LanguagesService $languagesService,
        SeoUrlPlaceholderHandlerInterface $seoUrlService
    ) {
        $this->_sdkFactory                  = $sdkFactory;
        $this->_productRepository           = $productRepository;
        $this->_salesChannelService         = $salesChannelService;
        $this->_categoryTransformerService  = $categoryTransformerService;
        $this->_fileLoader                  = $fileLoader;
        $this->_loggerFactory               = $loggerFactory;
        $this->_settingsService             = $settingsService;
        $this->_languagesService            = $languagesService;
        $this->_seoUrlService               = $seoUrlService;
    }

    /**
     * Build the catalog XML for a specific sales channel.
     *
     * @param SalesChannelEntity $salesChannelEntity
     * @return string
     * @throws FileCreationFailedException
     * @throws NoProductsFoundException
     * @throws SalesChannelNotFoundException
     */
    public function buildCatalogXmlForSalesChannel(SalesChannelEntity $salesChannelEntity): string
    {
        $this->_salesChannel        = $salesChannelEntity;
        $this->_logger              = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
        $this->_settings            = $this->_settingsService->getSettings($salesChannelEntity->getId());
        $this->_languages           = $this->_languagesService->getLanguages($salesChannelEntity);
        $this->_attributesHelper    = new AttributesHelper($this->_languages);
        $productIterator            = $this->getProductIterator($this->_salesChannel);
        $total                      = $productIterator->getTotal();

        if ($total <= 0) {
            throw new NoProductsFoundException($this->_salesChannel->getId());
        }

        $this->_fileLocation        = FileHelper::generateFile(FileHelper::DIRECTION_TYPE_EXPORT, static::CONTENT_TYPE, $this->_salesChannel->getId());
        $this->_xmlHelper           = FileHelper::getXmlHelperInstance($this->_fileLocation, static::XML_ROOT_ELEMENT);

        while ($productResult = $productIterator->fetch()) {
            /**
             * @var ProductEntity $product
             */
            foreach ($productResult->getEntities() as $product) {
                if (is_null($product->getParentId()) && $product->getActive()) {
                    $productArray = $this->getProductArray($product);

                    if (!empty($productArray['options']['option']) && !empty($productArray['identifier']['_cdata'])) {
                        try {
                            $this->_xmlHelper->append($productArray, 'product');

                            $this->_logger->info('Added product to the export XML file.', [
                                'process'       => static::LOGGER_PROCESS,
                                'sales_channel' => [
                                    'id'    => $this->_salesChannel->getId(),
                                    'name'  => $this->_salesChannel->getName(),
                                ],
                                'product'       => $productArray['identifier']['_cdata']
                            ]);
                        } catch (DOMException $e) {
                            $this->_logger->warning('Failed to convert product array to XML or appending it to the XML file.', [
                                'process'       => static::LOGGER_PROCESS,
                                'message'       => $e->getMessage(),
                                'sales_channel' => [
                                    'id'    => $this->_salesChannel->getId(),
                                    'name'  => $this->_salesChannel->getName(),
                                ],
                                'product'       => $productArray['identifier']['_cdata']
                            ]);
                        }
                    } elseif (empty($productArray['options']['option']) && !empty($productArray['identifier']['_cdata'])) {
                        $this->_logger->notice('No (active) product options present.', [
                            'process'       => static::LOGGER_PROCESS,
                            'sales_channel' => [
                                'id'    => $this->_salesChannel->getId(),
                                'name'  => $this->_salesChannel->getName(),
                            ],
                            'product'       => $productArray['identifier']['_cdata']
                        ]);
                    } else {
                        $this->_logger->notice('Product is not valid (no identifier).', [
                            'process'       => static::LOGGER_PROCESS,
                            'sales_channel' => [
                                'id'    => $this->_salesChannel->getId(),
                                'name'  => $this->_salesChannel->getName(),
                            ],
                            'product'       => $product->getId()
                        ]);
                    }
                }
            }
        }

        $this->_xmlHelper->endTransaction();

        return realpath($this->_fileLocation);
    }

    /**
     * Get the product iterator.
     *
     * @param SalesChannelEntity $salesChannelEntity
     * @return SalesChannelRepositoryIterator
     * @throws SalesChannelNotFoundException
     */
    protected function getProductIterator(SalesChannelEntity $salesChannelEntity): SalesChannelRepositoryIterator
    {
        $offset                     = 0;
        $criteria                   = new Criteria();
        $this->_salesChannelContext = $this->_salesChannelService->getSalesChannelContext($salesChannelEntity->getId());
        $associations               = [
            'categories',
            'cover',
            'cover.media',
            'manufacturer',
            'manufacturer.translations',
            'deliveryTime',
            'deliveryTime.translations',
            'seoUrls',
            'seoUrls.language',
            'seoUrls.language.locale',
            'seoUrls.language.parent',
            'seoUrls.language.parent.locale',
            'seoUrls.url',
            'tags',
            'media',
            'options',
            'options.translations',
            'options.translations.language',
            'options.translations.language.locale',
            'options.translations.language.parent',
            'options.translations.language.parent.locale',
            'options.group',
            'options.group.translations',
            'options.group.translations.language',
            'options.group.translations.language.locale',
            'options.group.translations.language.parent',
            'options.group.translations.language.parent.locale',
            'media.media',
            'prices',
            'prices.rule',
            'prices.rule.conditions',
            'translations',
            'translations.language',
            'translations.language.locale',
            'translations.language.parent',
            'translations.language.parent.locale',
            'properties',
            'properties.translations',
            'properties.translations.language',
            'properties.translations.language.locale',
            'properties.translations.language.parent',
            'properties.translations.language.parent.locale',
            'properties.group',
            'properties.group.translations',
            'properties.group.translations.language',
            'properties.group.translations.language.locale',
            'properties.group.translations.language.parent',
            'properties.group.translations.language.parent.locale',
            'children'
        ];

        $criteria
            ->setOffset($offset)
            ->setLimit(static::PAGE_SIZE)
            ->addAssociations($associations);

        foreach ($associations as $association) {
            $criteria->addAssociation('children.' . $association);
        }

        return new SalesChannelRepositoryIterator($this->_productRepository, $this->_salesChannelContext, $criteria);
    }

    /**
     * Get the product in an array format with the EffectConnect Marketplaces SDK expected values.
     *
     * @param ProductEntity $product
     * @return array
     */
    protected function getProductArray(ProductEntity $product): array
    {
        $productArray                   = [];
        $productArray['identifier']     = [ '_cdata' => strval($product->getId())];
        $productArray['brand']          = '';
        $productArray['categories']     = [
            'category'  => $this->_categoryTransformerService->getProductCategoryTree($product, $this->_salesChannelContext, $this->_languages)
        ];
        $productArray['options']        = [
            'option'    => $this->getProductOptionsArray($product)
        ];

        $productArray['brand']          = [ '_cdata' => $this->getProductBrand($product) ];

        if (empty($productArray['brand']) || empty($productArray['brand']['_cdata'])) {
            unset($productArray['brand']);
        }

        if (empty($productArray['categories']['category'])) {
            unset($productArray['categories']['category']);
        }

        if (empty($productArray['categories'])) {
            unset($productArray['categories']);
        }

        return $productArray;
    }

    /**
     * Get the product options for a product in an array format with the EffectConnect Marketplaces SDK expected values.
     *
     * @param ProductEntity $product
     * @return array
     */
    protected function getProductOptionsArray(ProductEntity $product): array
    {
        $childrenArray  = [];
        $children       = new ProductCollection([]);

        if ($product->getChildCount() <= 0 || is_null($product->getChildren())) {
            $children = new ProductCollection([$product]);
        } else {
            $children = $product->getChildren();
        }

        foreach ($children as $child) {
            if ($child->getActive()) {
                $childArray = $this->getProductOptionArray($child, $product);

                if (!empty($childArray['identifier']['_cdata'])) {
                    $childrenArray[] = $childArray;

                    $this->_logger->info('Added product option to the product data.', [
                        'process'       => static::LOGGER_PROCESS,
                        'sales_channel' => [
                            'id'    => $this->_salesChannel->getId(),
                            'name'  => $this->_salesChannel->getName(),
                        ],
                        'product'       => $product->getId(),
                        'option'        => $childArray['identifier']['_cdata']
                    ]);
                } else {
                    $this->_logger->notice('Product option is not valid (no identifier).', [
                        'process'       => static::LOGGER_PROCESS,
                        'sales_channel' => [
                            'id'    => $this->_salesChannel->getId(),
                            'name'  => $this->_salesChannel->getName(),
                        ],
                        'product'       => $product->getId(),
                        'option'        => $child->getId()
                    ]);
                }
            } else {
                $this->_logger->info('Product option not active.', [
                    'process'       => static::LOGGER_PROCESS,
                    'sales_channel' => [
                        'id'    => $this->_salesChannel->getId(),
                        'name'  => $this->_salesChannel->getName(),
                    ],
                    'product'       => $product->getId(),
                    'option'        => $child->getId()
                ]);
            }
        }

        return $childrenArray;
    }

    /**
     * Get a product option in an array format with the EffectConnect Marketplaces SDK expected values.
     * The parent will be used property inheritance (optional).
     *
     * @param ProductEntity $product
     * @param ProductEntity $parent
     * @return array
     */
    protected function getProductOptionArray(ProductEntity $product, ProductEntity $parent = null): array
    {
        $defaultLanguageCodes                           = [];
        $productOptionArray                             = [];
        $productOptionArray['identifier']               = [ '_cdata' => strval($product->getId())];
        $productOptionArray['cost']                     = $this->getProductCost($product);
        $productOptionArray['price']                    = $this->getProductSpecialPrice($product);
        $productOptionArray['priceOriginal']            = is_null($product->getPrice()) ? 0 : $product->getPrice()->first()->getGross();
        $productOptionArray['titles']                   = ['title' => []];
        $productOptionArray['urls']                     = ['url' => $this->getProductUrls($product)];
        $productOptionArray['descriptions']             = ['description' => []];
        $productOptionArray['ean']                      = [ '_cdata' => strval($product->getEan() ?? '')];
        $productOptionArray['sku']                      = [ '_cdata' => strval($product->getProductNumber())];
        $productOptionArray['deliveryTime']             = [ '_cdata' => $this->getProductDeliveryTime($product)];
        $productOptionArray['images']                   = ['image' => $this->getProductImages($product)];
        $productOptionArray['attributes']               = ['attribute' => []];
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

        $this->_attributesHelper->setTranslatedXmlValues($productOptionArray['titles']['title'], $product, 'name', $parent);
        $this->_attributesHelper->setTranslatedXmlValues($productOptionArray['descriptions']['description'], $product, 'description', $parent);

        $productOptionArray['attributes']['attribute']  = $this->getProductAttributes($product);

        $this->removeFromArrayWhenEmpty($productOptionArray, [
            'cost',
            'ean',
            'deliveryTime',
            'titles.title',
            'urls.url',
            'descriptions.description',
            'images.image',
            'attributes.attribute'
        ]);

        if (isset($productOptionArray['ean'])) {
            if (
                isset($productOptionArray['ean']['_cdata']) &&
                strlen($productOptionArray['ean']['_cdata']) === 12 &&
                $this->_settings->isAddLeadingZeroToEan()
            ) {
                $productOptionArray['ean']['_cdata'] = '0' . $productOptionArray['ean']['_cdata'];
            }

            if (!BarcodeValidator::IsValidEAN13($productOptionArray['ean']['_cdata'] ?? '')) {
                unset($productOptionArray['ean']);
            }
        }

        return $productOptionArray;
    }

    /**
     * Get product stock.
     *
     * @param ProductEntity $product
     * @return int
     */
    protected function getProductStock(ProductEntity $product): int
    {
        $stock                                          = 0;

        switch ($this->_settings->getStockType()) {
            case SettingStruct::STOCK_TYPE_PHYSICAL:
                $stock                                  = $product->getStock();
            case SettingStruct::STOCK_TYPE_SALABLE:
            default:
                $stock                                  = $product->getAvailableStock() ?? $product->getStock();
        }

        return intval(is_null($stock) || $stock < 0 ? 0 : $stock);
    }

    /**
     * Get product delivery time.
     *
     * @param ProductEntity $product
     * @return string
     */
    protected function getProductDeliveryTime(ProductEntity $product): string
    {
        if (!is_null($product->getDeliveryTime())) {
            $name = $product->getDeliveryTime()->get('name');
            if (!empty($name)) {
                return strval($name);
            }

            // delivery time is not translatable in EffectConnect, so one language is picked.
            $language   = ($this->_languages->getSystemDefaultLanguage() ?? $this->_languages->getSalesChannelDefaultLanguage()) ?? $this->_languages->getLanguages()[0];
            $value      = $this->_languages->getTranslation($product->getDeliveryTime(), 'name', $language, true) ?? '';

            return $value;
        }

        return '';
    }

    /**
     * Get product brand.
     *
     * @param ProductEntity $product
     * @return string
     */
    protected function getProductBrand(ProductEntity $product): string
    {
        if (!is_null($product->getManufacturer())) {
            $name = $product->getManufacturer()->get('name');
            if (!empty($name)) {
                return strval($name);
            }

            // brand is not translatable in EffectConnect, so one language is picked.
            $language   = ($this->_languages->getSystemDefaultLanguage() ?? $this->_languages->getSalesChannelDefaultLanguage()) ?? $this->_languages->getLanguages()[0];
            $value      = $this->_languages->getTranslation($product->getManufacturer(), 'name', $language, true) ?? '';

            return $value;
        }

        return '';
    }

    /**
     * Get the product's special price.
     *
     * @param ProductEntity $product
     * @return float
     */
    protected function getProductSpecialPrice(ProductEntity $product): float
    {
        if (!$this->_settings->isUseSpecialPrice()) {
            return is_null($product->getPrice()) ? 0 : $product->getPrice()->first()->getGross();
        }

        $foundPrices        = [];
        $alwaysValidRule    = new AlwaysValidRule();

        foreach ($product->getPrices() ?? [] as $price) {
            $rule = $price->getRule();

            if (is_null($rule)) {
                continue;
            }

            /**
             * @var RuleConditionEntity $condition
             */
            foreach($rule->getConditions() ?? [] as $condition) {
                if ($alwaysValidRule->getName() === $condition->getType()) {
                    if (!is_null($condition->getValue()) && isset($condition->getValue()['isAlwaysValid'])) {
                        $alwaysValid = $condition->getValue()['isAlwaysValid'];
                        if ($alwaysValid === true) {
                            $foundPrices[] = [
                                'priority'  => $rule->getPriority(),
                                'price'     => $price->getPrice()->first()->getGross()
                            ];
                        }
                    }
                }
            }
        }

        if (count($foundPrices) === 0) {
            return $product->getPrice()->first()->getGross();
        }

        usort($foundPrices, function ($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return $a['price'] > $b['price'];
            }

            return $a['priority'] > $b['priority'];
        });

        return $foundPrices[0]['price'];
    }

    /**
     * Get the product cost (purchase price).
     *
     * @param ProductEntity $product
     * @return float
     */
    protected function getProductCost(ProductEntity $product): float
    {
        // getPurchasePrice changed to getPurchasePrices in Shopware 6.4.
        // The if-statement below is to support backwards compatibility.
        if (SystemHelper::compareVersion('6.4', '<')) {
            // Shopware 6.3 and lower.

            return $product->getPurchasePrice() ?? 0;
        } else {
            // Shopware 6.4 and higher.

            if (!is_null($product->getPurchasePrices())) {
                $price = $product->getPurchasePrices()->first();
                return !is_null($price) ? $price->getGross() : 0;
            } else {
                return 0;
            }
        }
    }

    /**
     * Get the product's URL(s).
     *
     * @param ProductEntity $product
     * @return array
     */
    protected function getProductUrls(ProductEntity $product): array
    {
        $domains    = [];
        $urls       = [];

        foreach ($this->_salesChannel->getDomains() ?? [] as $domain) {
            $url                                = $domain->getUrl();

            if (isset($domains[$domain->getLanguageId()]) && $domains[$domain->getLanguageId()] !== $url) {
                if (substr($domains[$domain->getLanguageId()], 0, 8) !== "https://" && substr($url, 0, 8) === "https://") {
                    $domains[$domain->getLanguageId()] = $url;
                }

                continue;
            }

            $domains[$domain->getLanguageId()]  = $url;
        }

        foreach ($domains as $languageId => $domain) {
            $languageCode = $this->_languagesService->getLanguageCode($this->_languagesService->getLanguageById($languageId));
            $urlFormat = $this->_seoUrlService->generate('frontend.detail.page', [
                'productId' => $product->getId()
            ]);
            $url = $this->_seoUrlService->replace($urlFormat, $domain, $this->_salesChannelContext);
            $this->_attributesHelper->addXmlValue($urls, $url, $languageCode);
        }

        return $urls;
    }

    /**
     * Get the product's image(s)
     *
     * @param ProductEntity $product
     * @return array
     */
    protected function getProductImages(ProductEntity $product): array
    {
        $images             = [];
        $counter            = 0;
        $cover              = $product->getCover();
        $productMediaArray  = [];

        if (!is_null($cover)) {
            $media = $cover->getMedia();

            if (!is_null($media)) {
                $id         = $media->getId();
                $mediaArray = $this->getImageArray($media, $counter);

                if (!is_null($mediaArray)) {
                    $images[$id] = $mediaArray;
                    $counter++;
                }
            }
        }

        foreach ($product->getMedia() ?? [] as $productMedia) {
            if (!is_null($productMedia)) {
                $productMediaArray[] = $productMedia;
            }
        }

        usort($productMediaArray, function($a, $b) {
            /** @var ProductMediaEntity $b *//** @var ProductMediaEntity $a */
            return $a->getPosition() > $b->getPosition();
        });

        foreach ($productMediaArray as $productMedia) {
            $media      = $productMedia->getMedia();

            if (is_null($media)) {
                continue;
            }

            $id         = $media->getId();
            $mediaArray = $this->getImageArray($media, $counter);

            if (!is_null($mediaArray) && !isset($images[$id])) {
                $images[$id] = $mediaArray;
                $counter++;
            }
        }

        return array_slice(array_values($images), 0, 10, true);
    }

    /**
     * Get a media item in an array format with the EffectConnect Marketplaces SDK expected values.
     *
     * @param MediaEntity $media
     * @param int $order
     * @return array|null
     */
    protected function getImageArray(MediaEntity $media, int $order): ?array
    {
        $mediaType = $media->getMediaType();

        if (is_null($mediaType) || $mediaType->getName() !== (new ImageType())->getName()) {
            return null;
        }

        $size   = $media->getFileSize();

        try {
            $contents   = $this->_fileLoader->loadMediaFile($media->getId(), $this->_salesChannelContext->getContext());
            $md5        = md5($contents);
        } catch (Exception $e) {
            $md5        = null;
        }

        $image  = [
            'url'           => $media->getUrl(),
            'size'          => $size,
            'md5checksum'   => $md5,
            'order'         => $order
        ];

        $this->removeFromArrayWhenEmpty($image, [
            'size',
            'md5checksum'
        ]);

        return $image;
    }

    /**
     * Get the product's attributes in an array format with the EffectConnect Marketplaces SDK expected values.
     *
     * @param ProductEntity $product
     * @return array
     */
    protected function getProductAttributes(ProductEntity $product): array
    {
        $attributes         = [];
        $optionGroups       = [];

        foreach ($product->getOptions() ?? [] as $option) {
            $attributes[]   = $this->_attributesHelper->getVariableTranslatableAttributeArray($option);
            $optionGroups[] = $option->getGroupId();
        }

        foreach ($product->getProperties() ?? [] as $property) {
            if (in_array($property->getGroupId(), $optionGroups)) {
                continue;
            }

            $attributes[]   = $this->_attributesHelper->getVariableTranslatableAttributeArray($property);
        }

        $attributes[]       = $this->_attributesHelper->getStaticAttributeArray('Weight', $product->getWeight());
        $attributes[]       = $this->_attributesHelper->getStaticAttributeArray('Width', $product->getWidth());
        $attributes[]       = $this->_attributesHelper->getStaticAttributeArray('Height', $product->getHeight());
        $attributes[]       = $this->_attributesHelper->getStaticAttributeArray('Length', $product->getLength());
        $attributes[]       = $this->_attributesHelper->getStaticAttributeArray('Closeout', (($product->getIsCloseout() ?? false) ? 'Yes' : 'No'));

        if ($product->has('isNew') && is_bool($product->get('isNew'))) {
            $attributes[]   = $this->_attributesHelper->getStaticAttributeArray('New', ($product->get('isNew') ? 'Yes' : 'No'));
        }

        if (!is_null($product->getReleaseDate())) {
            $attributes[]   = $this->_attributesHelper->getStaticAttributeArray('Release Date', $product->getReleaseDate()->format('Y-m-d H:i:s'));
        }

        if (!is_null($product->getTags())) {
            $attributes[]   = $this->_attributesHelper->getStaticAttributeArray('Tags', array_map(function (TagEntity $tag) {
                return $tag->getName();
            }, $product->getTags()->getElements()));
        }

        if (!is_null($product->getManufacturerNumber())) {
            $attributes[]   = $this->_attributesHelper->getStaticAttributeArray('Manufacturer Number', $product->getManufacturerNumber());
        }

        if (!is_null($product->getPrice()) && $product->getPrice()->count() > 0 && !is_null($product->getPrice()->first())) {
            $price          = $product->getPrice()->first();
            $attributes[]   = $this->_attributesHelper->getStaticAttributeArray('Price (Net)', $price->getNet());
            $attributes[]   = $this->_attributesHelper->getStaticAttributeArray('Price (Gross)', $price->getGross());

            if (!is_null($price->getListPrice())) {
                $attributes[]   = $this->_attributesHelper->getStaticAttributeArray('List Price (Net)', $price->getListPrice()->getNet());
                $attributes[]   = $this->_attributesHelper->getStaticAttributeArray('List Price (Gross)', $price->getListPrice()->getGross());
            }
        }

        $attributes[]   = $this->_attributesHelper->getStaticTranslatableAttributeArray('Manufacturer', $product->getManufacturer(), 'name');
        $attributes[]   = $this->_attributesHelper->getStaticTranslatableAttributeArray('Meta Title', $product, 'metaTitle');
        $attributes[]   = $this->_attributesHelper->getStaticTranslatableAttributeArray('Meta Description', $product, 'metaDescription');
        $attributes[]   = $this->_attributesHelper->getStaticTranslatableAttributeArray('Keywords', $product, 'keywords');

        return $this->_attributesHelper->mergeAttributes(array_filter($attributes));
    }

    /**
     * Remove empty values from an array.
     *
     * @param array $array
     * @param array $items
     * @return void
     */
    protected function removeFromArrayWhenEmpty(array &$array, array $items)
    {
        foreach ($items as $item) {
            $itemParts = explode('.', $item);

            if (count($itemParts) === 2) {
                if (empty($array[$itemParts[0]][$itemParts[1]])) {
                    unset($array[$itemParts[0]]);
                }
            } else {
                if (empty($array[$itemParts[0]]) ||
                    (
                        is_array($array[$itemParts[0]]) &&
                        isset($array[$itemParts[0]]['_cdata']) &&
                        empty($array[$itemParts[0]]['_cdata'])
                    )
                ) {
                    unset($array[$itemParts[0]]);
                }
            }
        }
    }
}