<?php

namespace Itseasy\Test;

// use DI;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'service' => [
        'factories' => [
            Provider\IdentityProvider::class => InvokableFactory::class,
            ModuleTest\Service\TestService::class => InvokableFactory::class,
        ],
        'abstract_factories' => [
            Service\SimpleAbstractFactory::class,
        ],
        'aliases' => [
            'testalias' => Provider\IdentityProvider::class,
        ],
    ],
];
