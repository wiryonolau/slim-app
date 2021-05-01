<?php
declare(strict_types = 1);

namespace Itseasy\Guard\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Guard\RouteGuard;
use Itseasy\Guard\RouteGuardMiddleware;
use Itseasy\View\Helper\UrlHelper;

class RouteGuardMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : RouteGuardMiddleware
    {
        $routeGuard = $container->get(RouteGuard::class);
        $urlHelper  = $container->get(UrlHelper::class);
        return new RouteGuardMiddleware($routeGuard, $urlHelper);
    }
}
