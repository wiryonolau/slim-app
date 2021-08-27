<?php
declare(strict_types = 1);

namespace Itseasy\Identity;

interface IdentityInterface
{
    public function getRoles() : array;
}
