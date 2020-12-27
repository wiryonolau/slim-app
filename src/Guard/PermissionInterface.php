<?php

namespace Itseasy\Guard;

interface PermissionInterface {
    public function can(string $method, string $action) : bool;
}
