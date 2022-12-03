<?php

namespace Itseasy\Test\Action;

use DI;

return [
    "action" => [
        "factories" => [
            TestAction::class => DI\create(),
        ]
    ],
];
