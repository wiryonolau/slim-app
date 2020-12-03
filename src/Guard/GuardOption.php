<?php

namespace App\Guard;

use App\Guard\ArrayRoleProvider;

class GuardOption {

    protected $identity_provider;
    protected $role_provider;
    protected $login_route;
    protected $logout_route;
    protected $default_role = "guest";
    protected $whitelist;
    protected $roles = [];

    public function __construct(array $config = []) {
        if (isset($config["identity_provider"])) {
            $this->identity_provider = $config["identity_provider"];
        }

        if (isset($config["authorization"])) {
            $authorization_config = $config["authorization"];
            if (isset($authorization_config["role_provider"])) {
                $this->role_provider = $authorization_config["role_provider"];
            } else {
                $this->role_provider = ArrayRoleProvider::class;
            }

            if (isset($authorization_config["whitelist"])) {
                $this->whitelist = $authorization_config["whitelist"];
            }

            if (isset($authorization_config["default_role"])) {
                $this->default_role = $authorization_config["default_role"];
            }

            if (isset($authorization_config["roles"])) {
                $this->roles = $authorization_config["roles"];
            }
        }
    }

    public function getIdentityProvider() : string {
        return $this->identity_provider;
    }

    public function getRoleProvider() : string {
        return $this->role_provider;
    }

    public function getLoginRoute() : string {
        return $this->login_route;
    }

    public function getLogoutRoute() : string {
        return $this->logout_route;
    }

    public function getDefaultRole() : string {
        return $this->default_role;
    }

    public function getWhitelist() : array {
        return $this->whitelist;
    }

    public function getRoles() : array {
        return $this->roles;
    }
}
