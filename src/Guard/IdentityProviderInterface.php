<?php
declare(strict_types = 1);

namespace Itseasy\Guard;

use Itseasy\Guard\IdentityInterface;

interface IdentityProviderInterface
{
    public function hasIdentity() : bool;
    public function getIdentity() : IdentityInterface;
}
