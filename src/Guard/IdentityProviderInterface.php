<?php

namespace Itseasy\Guard;

use Itseasy\Guard\IdentityInterface;

interface IdentityProviderInterface {
    public function hasIdentity() : bool;
    public function getIdentity() : IdentityInterface;
}
