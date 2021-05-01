<?php
declare(strict_types = 1);

namespace Itseasy\Guard;

use Itseasy\Guard\IdentityProviderInterface;

interface RouteGuardInterface
{
    public function getIdentityProvider() : IdentityProviderInterface;
    public function allow(string $method, string $action) : bool;
}
