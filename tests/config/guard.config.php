<?php

namespace App\Test;

use App\Guard\ArrayRoleProvider;

return [
    "guard" => [
        "identity_provider" => Provider\IdentityProvider::class,
        "authentication" => [
            "login" => "/user/login",
            "logout" => "/user/logout"
        ],
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
