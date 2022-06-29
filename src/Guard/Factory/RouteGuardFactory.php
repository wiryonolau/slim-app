<?php

declare(strict_types=1);

namespace Itseasy\Guard\Factory;

use Itseasy\Guard\GuardOption;
use Itseasy\Guard\RouteGuard;
use Psr\Container\ContainerInterface;

class RouteGuardFactory
{
    public function __invoke(ContainerInterface $container): RouteGuard
    {
        $guardOption = $container->get(GuardOption::class);

        $identityProvider = $container->get($guardOption->getIdentityProvider());
        $roleProvider = $container->get($guardOption->getRoleProvider());

        return new RouteGuard($identityProvider, $roleProvider, $guardOption);
    }
}
