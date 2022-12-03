<?php

namespace Itseasy\Test\ModuleTest;

class Module
{
    public static function getConfigPath(): array
    {
        return [
            __DIR__ . "/config/*.config.php",
            __DIR__ . "/config/development/*.config.php"
        ];
    }
}
