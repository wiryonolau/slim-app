<?php

namespace App\Guard;

use App\Guard\PermissionInterface;

class Permission implements PermissionInterface {
    protected $method;
    protected $action;

    public function __construct($method, string $action) {
        $this->method = $method;
        $this->action = $action;
    }

    public function can(string $method, string $action) : bool {
        if ($action != $this->action) {
            return false;
        }

        if ($this->method == "*") {
            return true;
        }

        if (is_array($this->method)) {
            return in_array($method, $this->method);
        }

        return false;

    }
}