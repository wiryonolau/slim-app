<?php

namespace Itseasy\Test\Provider;

use Itseasy\Identity\IdentityInterface;
use Itseasy\Identity\IdentityProviderInterface;

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
