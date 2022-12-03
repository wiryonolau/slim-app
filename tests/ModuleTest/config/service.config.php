<?php

namespace Itseasy\Test\ModuleTest;

use Itseasy\Test\ModuleTest\Service\ModuleTestService;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    "service" => [
        "factories" => [
            ModuleTestService::class => InvokableFactory::class
        ]
    ],
];
