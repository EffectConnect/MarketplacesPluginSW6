<?php

namespace EffectConnect\Marketplaces\Core\ExportQueue;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ExportQueueDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ec_export_queue';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ExportQueueEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('type', 'type'))->addFlags(new Required()),
            (new IdField('identifier', 'identifier'))->addFlags(new Required()),
            (new IdField('sales_channel_id', 'salesChannelId')),
            (new JsonField('data', 'data'))->addFlags(new Required()),
            (new StringField('status', 'status'))->addFlags(new Required()),
        ]);
    }
}