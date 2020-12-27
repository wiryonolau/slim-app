<?php

namespace Itseasy\Test;

use DI;

return [
    "service" => [
        "factories" => [
            Provider\IdentityProvider::class => DI\create()
        ]
    ],
];
