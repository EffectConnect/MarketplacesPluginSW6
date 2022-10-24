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
    private EntityRepository $repository;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(ExportQueueEntity $queue) {
        $queue->setId(Uuid::randomHex());
        $this->repository->create([$queue->jsonSerialize()], Context::createDefaultContext());
    }

    /**
     * @param string $salesChannelId
     * @param string|null $type
     * @return ExportQueueEntity[]
     */
    public function getInQueue(string $salesChannelId, string $type = null): array
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('status', ExportQueueStatus::QUEUED))
            ->addFilter(new EqualsFilter('salesChannelId', $salesChannelId))
        ;
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