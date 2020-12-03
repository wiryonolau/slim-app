<?php

namespace App\Guard;

use App\Guard\PermissionInterface;

interface RoleInterface {
    public function getName() : string;
    public function can(string $method, string $action) : bool;
    public function addPermission(PermissionInterface $permission) : void;
}
