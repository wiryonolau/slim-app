<?php
declare(strict_types = 1);

namespace Itseasy\Guard;

use Itseasy\Guard\IdentityInterface;

interface RouteGuardInterface
{
    public function getIdentityProvider() : IdentityInterface;
    public function allow(string $method, string $action) : bool;
}
