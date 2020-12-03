<?php

namespace App\Test\Provider;

use App\Guard\IdentityInterface;
use App\Guard\IdentityProviderInterface;

class Identity implements IdentityInterface {
    public function getRoles() : array {
        return [];
    }
}

class IdentityProvider implements IdentityProviderInterface {
    public function hasIdentity() : bool {
        return true;
    }
    
    public function getIdentity() : IdentityInterface {
        return new Identity();
    }
}
