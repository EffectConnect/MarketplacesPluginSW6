<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use EffectConnect\Marketplaces\ScheduledTask\CatalogExportTask;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class AbstractTaskController extends AbstractController
{
    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    private function dispatch($message, array $stamps = []): Envelope
    {
        return $this->messageBus->dispatch(Envelope::wrap($message, $stamps), $stamps);
    }

    #[Route(path: '/trigger/{salesChannelId}/{type}', name: 'trigger', methods: ['POST'])]
    public function triggerTask(Request $request, Context $context): JsonResponse
    {
        $salesChannelId = $request->get('salesChannelId');
        $type = $request->get('type');

        switch ($type) {
            case 'catalog':
                $this->dispatch((new CatalogExportTask())->setSalesChannelId($salesChannelId));
                return new JsonResponse(['status' => 'OK']);
            default:
                return new JsonResponse(['status' => 'NOK']);
        }
    }
}