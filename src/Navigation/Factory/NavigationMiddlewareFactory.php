<?php
declare(strict_types = 1);

namespace Itseasy\Navigation\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Navigation\NavigationMiddleware;
use Itseasy\Navigation\Navigation;

class NavigationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : NavigationMiddleware
    {
        $navigation = $container->get(Navigation::class);
        return new NavigationMiddleware($navigation);
    }
}
