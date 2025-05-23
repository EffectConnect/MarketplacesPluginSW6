<?php

namespace EffectConnect\Marketplaces\Service;

use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueEntity;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueStatus;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ExportQueueService
{
    /** @var EntityRepository */
    private $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(ExportQueueEntity $queue) {
        $queue->setId(Uuid::randomHex());
        $this->repository->create([$queue->jsonSerialize()], Context::createDefaultContext());
    }

    /**
     * @param ExportQueueEntity $queue
     * @return bool
     */
    public function alreadyExist(ExportQueueEntity $queue): bool
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('type', $queue->getType()))
            ->addFilter(new EqualsFilter('identifier', $queue->getIdentifier()))
            ->addFilter(new EqualsFilter('status', $queue->getStatus()))
            ->addFilter(new EqualsFilter('salesChannelId', $queue->getSalesChannelId()))
            ->setLimit(1);

        return $this->repository->search($criteria, Context::createDefaultContext())->count() > 0;
    }

    /**
     * @param string $salesChannelId
     * @param string|null $type
     * @param int|null $limit
     * @return ExportQueueEntity[]
     */
    public function getInQueue(string $salesChannelId, string $type = null, ?int $limit = null): array
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('status', ExportQueueStatus::QUEUED))
            ->addFilter(new EqualsFilter('salesChannelId', $salesChannelId))
            ->setLimit($limit);

        if ($type !== null) {
            $criteria->addFilter(new EqualsFilter('type', $type));
        }
        return array_values($this->repository->search($criteria, Context::createDefaultContext())->getElements());
    }

    public function complete(array $ids) {
        $this->updateStatus($ids, ExportQueueStatus::COMPLETED);
    }

    public function start(array $ids) {
        $this->updateStatus($ids, ExportQueueStatus::STARTED);
    }

    private function updateStatus(array $ids, string $status) {
        $data = array_map(function ($id) use ($status) {return ['id' => $id, 'status' => $status];}, $ids);
        $this->repository->update(array_values($data), Context::createDefaultContext());
    }

}