<?php

namespace Itseasy\Test;

use DI;

return [
    'service' => [
        'factories' => [
            Provider\IdentityProvider::class => DI\create(),
            ModuleTest\Service\TestService::class => DI\create(),
        ],
        'abstract_factories' => [
            Service\SimpleAbstractFactory::class,
        ],
        'aliases' => [
            'testalias' => Provider\IdentityProvider::class,
        ],
    ],
];
