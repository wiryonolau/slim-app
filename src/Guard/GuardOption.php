<?php
declare(strict_types = 1);

namespace Itseasy\Guard;

use Itseasy\Guard\ArrayRoleProvider;

class GuardOption
{
    protected $identity_provider;
    protected $role_provider;
    protected $login_route = "/";
    protected $default_role = "guest";
    protected $use_redirect = true;
    protected $whitelist;
    protected $roles = [];

    public function __construct(array $config = [])
    {
        if (!empty($config["identity_provider"])) {
            $this->identity_provider = $config["identity_provider"];
        }

        if (!empty($config["login_route"])) {
            $this->login_route = $config["login_route"];
        }

        if (!empty($config["use_redirect"])) {
            $this->use_redirect = $config["use_redirect"];
        }

        if (!empty($config["authorization"])) {
            $authorization_config = $config["authorization"];
            if (!empty($authorization_config["role_provider"])) {
                $this->role_provider = $authorization_config["role_provider"];
            } else {
                $this->role_provider = ArrayRoleProvider::class;
            }

            if (!empty($authorization_config["whitelist"])) {
                $this->whitelist = $authorization_config["whitelist"];
            }

            if (!empty($authorization_config["default_role"])) {
                $this->default_role = $authorization_config["default_role"];
            }

            if (!empty($authorization_config["roles"])) {
                $this->roles = $authorization_config["roles"];
            }
        }
    }

    public function useRedirect() : bool
    {
        return $this->use_redirect;
    }

    public function getIdentityProvider() : string
    {
        return $this->identity_provider;
    }

    public function getRoleProvider() : string
    {
        return $this->role_provider;
    }

    public function getLoginRoute() : string
    {
        return $this->login_route;
    }

    public function getDefaultRole() : string
    {
        return $this->default_role;
    }

    public function getWhitelist() : array
    {
        return $this->whitelist;
    }

    public function getRoles() : array
    {
        return $this->roles;
    }
}
