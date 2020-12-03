<?php

namespace App\Guard;

use App\Guard\IdentityInterface;

interface IdentityProviderInterface {
    public function hasIdentity() : bool;
    public function getIdentity() : IdentityInterface;
}
