<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Transformer;

use EffectConnect\Marketplaces\Exception\CategoryNotFoundException;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Helper\AttributesHelper;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Object\LanguagesCollection;
use EffectConnect\Marketplaces\Service\LanguagesService;
use Monolog\Logger;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class CategoryTransformerService
 * @package EffectConnect\Marketplaces\Service\Transformer
 */
class CategoryTransformerService
{
    /**
     * The logger process for this transformer.
     */
    protected const LOGGER_PROCESS      = LoggerProcess::EXPORT_CATALOG;

    /**
     * @var EntityRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     * @var LoggerFactory
     */
    protected $_loggerFactory;

    /**
     * @var LanguagesService
     */
    protected $_languagesService;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var LanguagesCollection
     */
    protected $_languages;

    /**
     * @var AttributesHelper
     */
    protected $_attributesHelper;

    /**
     * CategoryTransformerService constructor.
     *
     * @param SalesChannelRepositoryInterface $categoryRepository
     * @param LoggerFactory $loggerFactory
     * @param LanguagesService $languagesService
     */
    public function __construct(
        SalesChannelRepositoryInterface $categoryRepository,
        LoggerFactory $loggerFactory,
        LanguagesService $languagesService
    ) {
        $this->_categoryRepository  = $categoryRepository;
        $this->_loggerFactory       = $loggerFactory;
        $this->_languagesService    = $languagesService;
        $this->_logger              = $this->_loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * Get the category tree array from a product in the EffectConnect Marketplaces SDK expected format.
     *
     * @param ProductEntity $product
     * @param SalesChannelContext $context
     * @return array
     */
    public function getProductCategoryTree(ProductEntity $product, SalesChannelContext $context, LanguagesCollection $languages): array
    {
        $this->_languages           = $languages;
        $this->_attributesHelper    = new AttributesHelper($this->_languages);
        $productCategoryIds = $product->getCategoryTree();
        $productCategories  = !is_null($productCategoryIds) && !empty($productCategoryIds) ? $this->getCategories($productCategoryIds, $context) : [];
        $categoriesArray    = [];

        /**
         * @var CategoryEntity $category
         */
        foreach ($productCategories as $category) {
            $layers             = array_keys($category->getPlainBreadcrumb());
            $currentArrayItem   = &$categoriesArray;
            $counter            = 0;

            foreach ($layers as $id) {
                $counter++;
                try {
                    $parentCategory = $this->getCategoryById($id, $context);
                } catch (CategoryNotFoundException $e) {
                    $this->_logger->notice('Parent category not found.', [
                        'process'       => static::LOGGER_PROCESS,
                        'message'       => $e->getMessage(),
                        'sales_channel' => [
                            'id'    => $context->getSalesChannel()->getId(),
                            'name'  => $context->getSalesChannel()->getName(),
                        ],
                        'product'       => $product->getId(),
                        'category'      => $id
                    ]);

                    continue 2;
                }

                if (isset($currentArrayItem[$parentCategory->getId()])) {
                    $currentArrayItem = &$currentArrayItem[$parentCategory->getId()]['children']['category'];
                    continue;
                }

                $currentArrayItem[$parentCategory->getId()]['id']       = $parentCategory->getAutoIncrement();
                $currentArrayItem[$parentCategory->getId()]['titles']   = [
                    'title' => []
                ];

                $translations = $languages->getTranslations($category, 'name');

                foreach ($translations as $languageCode => $translatedValue) {
                    $this->_attributesHelper->addXmlValue($currentArrayItem[$parentCategory->getId()]['titles']['title'], $translatedValue, $languageCode);
                }

                if (count($layers) === $counter) {
                    $currentArrayItem = &$categoriesArray;
                    continue;
                }

                if (!isset($currentArrayItem[$parentCategory->getId()]['children'])) {
                    $currentArrayItem[$parentCategory->getId()]['children'] = [
                        'category' => []
                    ];
                }

                if (!isset($currentArrayItem[$parentCategory->getId()]['children']['category'])) {
                    $currentArrayItem[$parentCategory->getId()]['children']['category'] = [];
                }

                if ($counter) {
                    $currentArrayItem = &$currentArrayItem[$parentCategory->getId()]['children']['category'];
                }
            }
        }

        $this->stripChildrenKeys($categoriesArray);

        return $categoriesArray;
    }

    /**
     * Get a category by it's ID.
     *
     * @param string $id
     * @param SalesChannelContext $context
     * @return CategoryEntity
     * @throws CategoryNotFoundException
     */
    public function getCategoryById(string $id, SalesChannelContext $context): CategoryEntity
    {
        /**
         * @var CategoryEntity $category
         */
        $category   = $this->getCategories([$id], $context)->get($id);

        if (is_null($category)) {
            throw new CategoryNotFoundException($id);
        }

        return $category;
    }

    /**
     * Get categories using multiple ID's.
     *
     * @param array $ids
     * @param SalesChannelContext $context
     * @return EntitySearchResult
     */
    public function getCategories(array $ids, SalesChannelContext $context): EntitySearchResult
    {
        $criteria   = new Criteria();

        $criteria
            ->setIds($ids)
            ->addAssociation('translations')
            ->addAssociation('translations.language')
            ->addAssociation('translations.language.locale')
            ->addAssociation('translations.language.parent')
            ->addAssociation('translations.language.parent.locale');

        return $this->_categoryRepository
            ->search($criteria, $context);
    }

    /**
     * Strip the categories children array's keys.
     *
     * @param array $categories
     */
    protected function stripChildrenKeys(array &$categories)
    {
        foreach ($categories as &$category) {
            if (!isset($category['children']['category'])) {
                continue;
            }

            $category['children']['category'] = array_values($category['children']['category']);

            $this->stripChildrenKeys($category['children']['category']);
        }

        $categories = array_values($categories);
    }
}