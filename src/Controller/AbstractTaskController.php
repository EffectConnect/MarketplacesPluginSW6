<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use EffectConnect\Marketplaces\ScheduledTask\CatalogExportTask;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\MessageQueue\MonitoringBusDecorator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AbstractTaskController extends AbstractController
{
    protected $messageBus;

    public function __construct(MonitoringBusDecorator $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/trigger/{salesChannelId}/{type}", methods={"POST"})
     */
    public function triggerTask(Request $request, Context $context): JsonResponse
    {
        $salesChannelId = $request->get('salesChannelId');
        $type = $request->get('type');

        switch ($type) {
            case 'catalog':
                $this->messageBus->dispatch((new CatalogExportTask())->setSalesChannelId($salesChannelId));
                break;
            case 'offer':
            case 'order':
                return new JsonResponse(['status' => 'NOK']);
        }
        return new JsonResponse(['status' => 'OK']);
    }
}