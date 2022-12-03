<?php

declare(strict_types=1);

namespace Itseasy\Guard\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Guard\GuardOption;
use Itseasy\Guard\ArrayRoleProvider;

class ArrayRoleProviderFactory
{
    public function __invoke(ContainerInterface $container): ArrayRoleProvider
    {
        $guardOption = $container->get(GuardOption::class);
        return new ArrayRoleProvider($guardOption->getRoles());
    }
}
