<?php

namespace App;

return [
    "service" => [
        "factories" => [
            Middleware\HttpRendererMiddleware::class => Middleware\Factory\HttpRendererMiddlewareFactory::class
        ]
    ],
    "asset" => [
        "resolver_configs" => [
            "paths" => [
            ]
        ],
    ]
];
