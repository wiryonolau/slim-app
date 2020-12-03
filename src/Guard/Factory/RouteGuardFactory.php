<?php

namespace App\Guard\Factory;

use Psr\Container\ContainerInterface;
use App\Guard\GuardOption;
use App\Guard\RouteGuard;

class RouteGuardFactory {
    public function __invoke(ContainerInterface $container) {
        $guardOption = $container->get(GuardOption::class);

        $identityProvider = $container->get($guardOption->getIdentityProvider());
        $roleProvider = $container->get($guardOption->getRoleProvider());

        return new RouteGuard($identityProvider, $roleProvider, $guardOption);
    }
}
