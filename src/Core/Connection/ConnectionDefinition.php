<?php

namespace EffectConnect\Marketplaces\Core\Connection;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ConnectionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ec_connection';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ConnectionEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new IdField('sales_channel_id', 'salesChannelId'))->addFlags(new Required()),
            new StringField('name',  'name'),
            new StringField('public_key',  'publicKey'),
            new StringField('secret_key',  'secretKey'),
            new IntField('catalog_export_schedule',  'catalogExportSchedule'),
            new BoolField('add_leading_zero_to_ean',  'addLeadingZeroToEan'),
            new BoolField('use_special_price',  'useSpecialPrice'),
            new BoolField('use_fallback_translations',  'useFallbackTranslations'),
            new BoolField('use_sales_channel_default_language_as_first_fallback_language',  'useSalesChannelDefaultLanguageAsFirstFallbackLanguage'),
            new BoolField('use_system_languages',  'useSystemLanguages'),
            new IntField('offer_export_schedule',  'offerExportSchedule'),
            new StringField('stock_type',  'stockType'),
            new IntField('order_import_schedule',  'orderImportSchedule'),
            new StringField('payment_status',  'paymentStatus'),
            new StringField('order_status',  'orderStatus'),
            new StringField('payment_method',  'paymentMethod'),
            new StringField('shipping_method',  'shippingMethod'),
            new BoolField('create_customer', 'createCustomer'),
            new StringField('customer_group', 'customerGroup'),
            new StringField('customer_source_type', 'customerSourceType'),
            new BoolField('import_externally_fulfilled_orders', 'importExternallyFulfilledOrders'),
            new StringField('external_order_status', 'externalOrderStatus'),
            new StringField('external_payment_status', 'externalPaymentStatus'),
            new StringField('external_shipping_status', 'externalShippingStatus'),
        ]);
    }
}