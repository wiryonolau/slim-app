<?php

namespace App\Guard;

use App\Guard\RoleInterface;
use App\Guard\Role;
use App\Guard\Permission;

class ArrayRoleProvider implements RoleProviderInterface {
    protected $roles = [];

    public function __construct(array $roles_config = []) {
        foreach($roles_config as $role_config) {
            $role = new Role($role_config["name"]);

            foreach ($role_config["permissions"] as $permission) {
                $permission = new Permission($permission["method"], $permission["action"]);
                $role->addPermission($permission);
            }

            $this->roles[$role->getName()] = $role;
        }
    }

    public function getRole(string $role_name) : RoleInterface {
        return $this->roles[$role_name];
    }
}
