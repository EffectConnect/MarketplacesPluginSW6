<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\Acl;

/**
 * @Route("api/ec/action/task")
 * @Acl({"sales_channel.viewer"})
 */
#[Route(defaults: ['_routeScope' => ['api']])]
class TaskController extends AbstractTaskController
{
}