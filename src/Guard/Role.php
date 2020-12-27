<?php

namespace Itseasy\Guard;

use Itseasy\Guard\RoleInterface;
use ArrayIterator;

class Role implements RoleInterface {
    protected $name;
    protected $permissions;

    public function __construct(string $name) {
        $this->name = $name;
        $this->permissions = new ArrayIterator();
    }

    public function getName() : string {
        return $this->name;
    }

    public function can(string $method, string $action) : bool {
        foreach ($this->permissions as $permission) {
            if ($permission->can($method, $action)) {
                return true;
            }
        }
        return false;
    }

    public function addPermission(PermissionInterface $permission) : void {
        $this->permissions->append($permission);
    }
}
