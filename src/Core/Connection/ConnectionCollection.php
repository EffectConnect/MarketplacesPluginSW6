<?php

namespace EffectConnect\Marketplaces\Core\Connection;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                  add(ConnectionEntity $entity)
 * @method void                  set(string $key, ConnectionEntity $entity)
 * @method ConnectionEntity[]    getIterator()
 * @method ConnectionEntity[]    getElements()
 * @method ConnectionEntity|null get(string $key)
 * @method ConnectionEntity|null first()
 * @method ConnectionEntity|null last()
 */
class ConnectionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ConnectionEntity::class;
    }
}