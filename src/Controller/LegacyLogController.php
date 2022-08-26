<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Annotation\Acl;

/**
 * @RouteScope(scopes={"api"})
 * @Route("api/v2/ec/action/log")
 * @Acl({"sales_channel.viewer"})
 */
class LegacyLogController extends AbstractLogController
{
}