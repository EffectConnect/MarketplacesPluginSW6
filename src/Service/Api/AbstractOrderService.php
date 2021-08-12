<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service\Api;

use EffectConnect\PHPSdk\Core\Model\Filter\HasStatusFilter;

/**
 * Class AbstractOrderService
 * @package EffectConnect\Marketplaces\Service\Api
 */
abstract class AbstractOrderService extends AbstractApiService
{
    /**
     * Order import failed tag.
     */
    const ORDER_IMPORT_FAILED_TAG       = 'order_import_failed';

    /**
     * Order import succeeded tag.
     */
    const ORDER_IMPORT_SUCCEEDED_TAG    = 'order_import_succeeded';

    /**
     * Status filters.
     */
    const STATUS_FILTERS                = [
        HasStatusFilter::STATUS_PAID
    ];

    /**
     * Exclude tag filters.
     */
    const EXCLUDE_TAG_FILTERS           = [
        self::ORDER_IMPORT_FAILED_TAG,
        self::ORDER_IMPORT_SUCCEEDED_TAG
    ];
}