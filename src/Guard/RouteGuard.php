<?php
declare(strict_types = 1);

namespace Itseasy\Guard;

use Itseasy\Guard\GuardOption;
use Itseasy\Guard\IdentityProviderInterface;
use Itseasy\Guard\RoleProviderInterface;
use Itseasy\Guard\RouteGuardInterface;
use Itseasy\Guard\IdentityInterface;

class RouteGuard implements RouteGuardInterface
{
    protected $identityProvider;
    protected $roleProvider;
    protected $options;

    public function __construct(IdentityProviderInterface $identityProvider, RoleProviderInterface $roleProvider, GuardOption $options)
    {
        $this->identityProvider = $identityProvider;
        $this->roleProvider = $roleProvider;
        $this->options = $options;
    }

    public function getIdentityProvider() : IdentityProviderInterface
    {
        return $this->identityProvider;
    }

    public function getOptions() : array
    {
        return $this->options;
    }

    public function allow(string $method, string $action) : bool
    {
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
