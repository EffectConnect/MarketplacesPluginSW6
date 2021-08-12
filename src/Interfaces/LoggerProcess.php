<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Interfaces;

/**
 * Class LoggerProcess
 * @package EffectConnect\Marketplaces\Interfaces
 */
class LoggerProcess
{
    /**
     * Export Catalog
     */
    public const EXPORT_CATALOG     = 'export_catalog';

    /**
     * Export Offers
     */
    public const EXPORT_OFFERS      = 'export_offers';

    /**
     * Import Orders
     */
    public const IMPORT_ORDERS      = 'import_orders';

    /**
     * Update Order
     */
    public const UPDATE_ORDER       = 'update_order';

    /**
     * Export Shipment
     */
    public const EXPORT_SHIPMENT    = 'export_shipment';

    /**
     * Frontend
     */
    public const FRONTEND           = 'frontend';

    /**
     * Installation
     */
    public const INSTALLATION       = 'installation';

    /**
     * Other
     */
    public const OTHER              = 'other';
}