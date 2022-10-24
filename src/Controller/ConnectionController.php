<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\Acl;

/**
 * @RouteScope(scopes={"api"})
 * @Route("api/ec/action/connection")
 * @Acl({"sales_channel.viewer"})
 */
class ConnectionController extends AbstractConnectionController
{
}