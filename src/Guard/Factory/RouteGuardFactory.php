<?php

namespace Itseasy\Guard\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Guard\GuardOption;
use Itseasy\Guard\RouteGuard;

class RouteGuardFactory {
    public function __invoke(ContainerInterface $container) {
        $guardOption = $container->get(GuardOption::class);

        $identityProvider = $container->get($guardOption->getIdentityProvider());
        $roleProvider = $container->get($guardOption->getRoleProvider());

        return new RouteGuard($identityProvider, $roleProvider, $guardOption);
    }
}
