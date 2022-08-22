<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Service;

use EffectConnect\Marketplaces\Core\Connection\ConnectionEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Class ConnectionService
 * @package EffectConnect\Marketplaces\Service
 */
class ConnectionService
{
    protected $connectionRepository;

    /**
     * ConnectionService constructor.
     *
     * @param EntityRepositoryInterface $connectionRepository
     */
    public function __construct(EntityRepositoryInterface $connectionRepository) {
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * @param ConnectionEntity $connection
     * @return void
     */
    public function create(ConnectionEntity $connection) {
        $connection->setId(Uuid::randomHex());
        $this->connectionRepository->create([$connection->jsonSerialize()], new Context(new SystemSource()));
    }

    /**
     * @param ConnectionEntity $connection
     * @return void
     */
    public function update(ConnectionEntity $connection) {
        $this->connectionRepository->update([$connection->jsonSerialize()], new Context(new SystemSource()));
    }

    /**
     * @param Filter $filter
     * @return ConnectionEntity|null
     */
    private function searchFilter(Filter $filter): ?ConnectionEntity
    {
        $result = $this->connectionRepository->search((new Criteria())->addFilter($filter), new Context(new SystemSource()));
        return $result->first();
    }

    /**
     * @param string $salesChannelId
     * @return ConnectionEntity|null
     */
    public function get(string $salesChannelId): ?ConnectionEntity
    {
        return $this->searchFilter(new EqualsFilter('salesChannelId', $salesChannelId));
    }

    /**
     * @param string $id
     * @return ConnectionEntity|null
     */
    public function getFromId(string $id): ?ConnectionEntity
    {
        return $this->searchFilter(new EqualsFilter('id', $id));
    }

    /**
     * @param array|null $includes
     * @return ConnectionEntity[]
     */
    public function getAll(?array $includes = null): array
    {
        $criteria = (new Criteria());
        $criteria->setIncludes($includes);
        $result = $this->connectionRepository->search($criteria, new Context(new SystemSource()));
        return array_values($result->getElements());
    }

    public function delete(string $id)
    {
        $this->connectionRepository->delete([['id' => $id]], new Context(new SystemSource()));
    }

}