<?php

namespace App\Guard\Factory;

use Psr\Container\ContainerInterface;
use App\Guard\GuardOption;
use App\Guard\ArrayRoleProvider;

class ArrayRoleProviderFactory {
    public function __invoke(ContainerInterface $container) {
        $guardOption = $container->get(GuardOption::class);
        return new ArrayRoleProvider($guardOption->getRoles());
    }
}
