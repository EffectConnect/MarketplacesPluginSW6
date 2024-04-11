<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use Symfony\Component\Routing\Attribute\Route;

/**
 * @Acl({"sales_channel.viewer"})
 */
#[Route(path: '/api/ec/action/task',defaults: ['_routeScope' => ['api']])]
class TaskController extends AbstractTaskController
{
}