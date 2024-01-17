<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\Acl;

/**
 * @Acl({"sales_channel.viewer"})
 */
#[Route(path: 'api/ec/action/log', defaults: ['_routeScope' => ['api']])]
class LogController extends AbstractLogController
{
}