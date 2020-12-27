<?php
namespace Itseasy\Test;

return [
    "console" => [
        "commands" => [
            Console\Command\TestCommand::class
        ],
        "factories" => [
            Console\Command\TestCommand::class => Console\Command\Factory\TestCommandFactory::class
        ]
    ]
];
