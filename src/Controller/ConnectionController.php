<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;


use Symfony\Component\Routing\Annotation\Route;

/**
 * @Acl({"sales_channel.viewer"})
 */
#[Route(path: '/api/ec/action/connection', defaults: ['_routeScope' => ['api']])]
class ConnectionController extends AbstractConnectionController
{

}
