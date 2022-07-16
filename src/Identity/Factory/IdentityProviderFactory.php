<?php

declare(strict_types=1);

namespace Itseasy\Identity\Factory;

use Itseasy\Identity\IdentityProvider;
use Itseasy\Identity\IdentityProviderInterface;
use Psr\Container\ContainerInterface;

class IdentityProviderFactory
{
    public function __invoke(ContainerInterface $container): IdentityProviderInterface
    {
        return new IdentityProvider();
    }
}
