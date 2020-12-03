<?php

namespace App\Guard;

interface RoleProviderInterface {
    public function getRole(string $role_name) : RoleInterface;
}
