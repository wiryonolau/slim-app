<?php

namespace App;

return [
    "service" => [
        "factories" => [
            Middleware\SessionMiddleware::class => Middleware\Factory\SessionMiddlewareFactory::class,
            Middleware\AssetMiddleware::class => Middleware\Factory\AssetMiddlewareFactory::class
        ]
    ],
    "session" => [
        'name' => 'App',
        'cache_expire' => 0
    ],
    "asset" => [
        "resolver_configs" => [
            "paths" => [
            ]
        ],
    ],
    "view" => [
        "renderer" => Http\Renderer\Factory\PhpRendererFactory::class
    ]
];
