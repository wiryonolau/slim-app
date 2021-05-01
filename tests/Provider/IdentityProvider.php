<?php

namespace Itseasy\Test\Provider;

use Itseasy\Guard\IdentityInterface;
use Itseasy\Guard\IdentityProviderInterface;

class Identity implements IdentityInterface
{
    public function getRoles() : array
    {
        return [];
    }
}

class IdentityProvider implements IdentityProviderInterface
{
    public function hasIdentity() : bool
    {
        return true;
    }

    public function getIdentity() : IdentityInterface
    {
        return new Identity();
    }
}
