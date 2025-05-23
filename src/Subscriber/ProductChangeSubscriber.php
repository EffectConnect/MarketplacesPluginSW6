<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Subscriber;

use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueEntity;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueStatus;
use EffectConnect\Marketplaces\Core\ExportQueue\ExportQueueType;
use EffectConnect\Marketplaces\Factory\LoggerFactory;
use EffectConnect\Marketplaces\Interfaces\LoggerProcess;
use EffectConnect\Marketplaces\Service\ExportQueueService;
use Monolog\Logger;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityEntity;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductChangeSubscriber
 * @package EffectConnect\Marketplaces\Subscriber
 */
class ProductChangeSubscriber implements EventSubscriberInterface
{
    /**
     * The logger process for this service.
     */
    protected const LOGGER_PROCESS = LoggerProcess::OFFER_CHANGE;

    /**
     * @var ExportQueueService
     */
    protected $exportQueueService;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var EntityRepository
     */
    private $productVisibilityRepository;

    /**
     * ProductChangeSubscriber constructor.
     *
     * @param EntityRepository $productVisibilityRepository
     * @param ExportQueueService $exportQueueService
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        EntityRepository $productVisibilityRepository,
        ExportQueueService $exportQueueService,
        LoggerFactory $loggerFactory
    ) {
        $this->productVisibilityRepository = $productVisibilityRepository;
        $this->exportQueueService = $exportQueueService;
        $this->logger = $loggerFactory::createLogger(static::LOGGER_PROCESS);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => 'onProductChange',
        ];
    }

    private function addProductChangeToQueue(string $productId, string $salesChannelId) {
        $queue = (new ExportQueueEntity())
            ->setIdentifier($productId)
            ->setSalesChannelId($salesChannelId)
            ->setData([])
            ->setType(ExportQueueType::OFFER)
            ->setStatus(ExportQueueStatus::QUEUED)
        ;

        if (!$this->exportQueueService->alreadyExist($queue)) {
            $this->exportQueueService->create($queue);
        }
    }

    /**
     * @param EntityWrittenEvent $event
     */
    public function onProductChange(EntityWrittenEvent $event) {
        foreach ($event->getWriteResults() as $writeResult) {
            if ($writeResult->getOperation() !== 'update') {
                continue;
            }

            $payload = $writeResult->getPayload();
            if (!is_array($payload) || (
                !array_key_exists('stock', $payload) &&
                !array_key_exists('availableStock', $payload) &&
                !array_key_exists('price', $payload) &&
                !array_key_exists('purchasePrices', $payload) &&
                !array_key_exists('deliveryTimeId', $payload)
            )) {
                continue;
            }

            $primaryKeys = is_array($writeResult->getPrimaryKey()) ? $writeResult->getPrimaryKey() : [$writeResult->getPrimaryKey()];
            $criteria = (new Criteria())->addFilter(new EqualsAnyFilter('productId', $primaryKeys));
            $visibilities = $this->productVisibilityRepository->search($criteria, Context::createDefaultContext());

            /** @var ProductVisibilityEntity $element */
            foreach($visibilities->getElements() as $element) {
                $this->addProductChangeToQueue($element->getProductId(), $element->getSalesChannelId());
            }
        }
    }
}