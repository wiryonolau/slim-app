<?php
declare(strict_types = 1);

namespace Itseasy\Guard;

interface IdentityInterface
{
    public function getRoles() : array;
}
