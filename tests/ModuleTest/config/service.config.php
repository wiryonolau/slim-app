<?php

namespace Itseasy\Test\ModuleTest;

use DI;

return [
    "service" => [
        "factories" => [
            Service\TestService::class => DI\factory(function() {})
        ]
    ],
];
