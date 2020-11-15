<?php

namespace App;

use DI;

return [
    "service" => [
        "factories" => [
            Middleware\HttpRendererMiddleware::class => Middleware\Factory\HttpRendererMiddlewareFactory::class
        ]
    ],
];

