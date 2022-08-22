<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use EffectConnect\Marketplaces\ScheduledTask\CatalogExportTask;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\MessageQueue\MonitoringBusDecorator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\Acl;

/**
 * @RouteScope(scopes={"api"})
 */
class TaskController extends AbstractController
{
    protected $messageBus;

    public function __construct(MonitoringBusDecorator $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @Route(
     *     "api/effectconnect/action/task/trigger/{salesChannelId}/{type}",
     *     name="api.effectconnect.action.task.trigger",
     *     methods={"POST"}
     * )
     * @Acl({"sales_channel.viewer"})
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