<?php

namespace App\Guard;

use App\Guard\IdentityInterface;

interface IdentityProviderInterface {
    public function getIdentity() : IdentityInterface;
}
