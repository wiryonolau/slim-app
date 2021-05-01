<?php
declare(strict_types = 1);

namespace Itseasy\Guard;

use Itseasy\Guard\RoleInterface;
use Itseasy\Guard\Role;
use Itseasy\Guard\Permission;

class ArrayRoleProvider implements RoleProviderInterface
{
    protected $roles = [];

    public function __construct(array $roles_config = [])
    {
        foreach ($roles_config as $role_config) {
            $role = new Role($role_config["name"]);

            foreach ($role_config["permissions"] as $permission) {
                $permission = new Permission($permission["method"], $permission["action"]);
                $role->addPermission($permission);
            }

            $this->roles[$role->getName()] = $role;
        }
    }

    public function getRole(string $role_name) : RoleInterface
    {
        return $this->roles[$role_name];
    }
}
