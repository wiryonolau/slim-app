<?php

namespace App\Guard;

interface PermissionInterface {
    public function can(string $method, string $action) : bool;
}
