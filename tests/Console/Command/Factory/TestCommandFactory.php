<?php

namespace App\Test\Console\Command\Factory;

use Psr\Container\ContainerInterface;
use App\Test\Console\Command\TestCommand;

class TestCommandFactory {
    public function __invoke(ContainerInterface $container) {
        return new TestCommand();
    }
}
