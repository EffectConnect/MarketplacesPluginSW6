<?php

namespace EffectConnect\Marketplaces\Core\ExportQueue;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(ExportQueueEntity $entity)
 * @method void                   set(string $key, ExportQueueEntity $entity)
 * @method ExportQueueEntity[]    getIterator()
 * @method ExportQueueEntity[]    getElements()
 * @method ExportQueueEntity|null get(string $key)
 * @method ExportQueueEntity|null first()
 * @method ExportQueueEntity|null last()
 */
class ExportQueueCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ExportQueueEntity::class;
    }
}