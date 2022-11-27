<?php

declare(strict_types=1);

namespace Itseasy\Guard;

interface RoleProviderInterface
{
    public function getRole(string $role_name): RoleInterface;
}
