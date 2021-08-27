<?php
declare(strict_types = 1);

namespace Itseasy\Identity;

use Itseasy\Identity\IdentityInterface;

interface IdentityProviderInterface
{
    public function hasIdentity() : bool;
    public function getIdentity() : IdentityInterface;
}
