<?php

namespace Itseasy\Test\Console\Command\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Test\Console\Command\TestCommand;

class TestCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new TestCommand();
    }
}
