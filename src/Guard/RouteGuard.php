<?php

namespace App\Guard;

use App\Guard\GuardOption;
use App\Guard\IdentityProviderInterface;
use App\Guard\RoleProviderInterface;
use App\Guard\RouteGuardInterface;

class RouteGuard implements RouteGuardInterface {
    protected $identityProvider;
    protected $roleProvider;
    protected $options;

    public function __construct(IdentityProviderInterface $identityProvider, RoleProviderInterface $roleProvider, GuardOption $options) {
        $this->identityProvider = $identityProvider;
        $this->roleProvider = $roleProvider;
        $this->options = $options;
    }

    public function allow(string $method, string $action) : bool {
        $identity = $this->identityProvider->getIdentity();
        $roles = $identity->getRoles();

        if (empty($roles)) {
            $roles = [$this->options->getDefaultRole()];
        }

        $whitelisted = array_intersect($roles, $this->options->getWhitelist());

        if (count($whitelisted)) {
            return true;
        }



        foreach ($roles as $role_name) {
            $role = $this->roleProvider->getRole($role_name);
            if ($role->can($method, $action)) {
                return true;
            }
        }
        return false;
    }
}
