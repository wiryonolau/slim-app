<?php
declare(strict_types = 1);

namespace Itseasy\Guard;

interface PermissionInterface
{
    public function can(string $method, string $action) : bool;
}
