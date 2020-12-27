<?php

namespace Itseasy\Test;

use Itseasy\Guard\ArrayRoleProvider;

return [
    "guard" => [
        "identity_provider" => Provider\IdentityProvider::class,
        "login_route" => "/user/login",
        "authorization" => [
            "default_role" => "guest",
            "whitelist" => [
                "administrator"
            ],
            "role_provider" => ArrayRoleProvider::class,
            "roles" => [
                [
                    "name" => "guest",
                    "permissions" => [
                        [
                            "action" => Action\TestAction::class,
                            "method" => ["GET"]
                        ],
                    ],
                ]
            ]
        ],
    ],
];
