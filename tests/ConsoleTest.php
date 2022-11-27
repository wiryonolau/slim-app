<?php

namespace Itseasy\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Application;
use Itseasy\ServiceManager;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;

final class ConsoleTest extends TestCase
{
    public function testConsole()
    {
        $app = new Application([
            'config_path' => [__DIR__ . '/config/*.config.php'],
            'module' => [
                \Laminas\Cache\Module::class,
                \Laminas\Cache\Storage\Adapter\Filesystem\Module::class,
            ],
            'container_provider' => ServiceManager\LaminasServiceManager::class,
        ]);
        $app->setApplicationType(Application::APP_CONSOLE)->build();

        $username = "yoyoyo";
        $command = $app->getApplication()->find('test');
        $commandTester = new CommandTester($command);
        $commandTester->execute(["username" => $username]);
        $output = $commandTester->getDisplay();
        $this->assertEquals($output, "Hello $username\n");
    }

    public function testAssetConsole()
    {
        $app = new Application([
            'config_path' => [__DIR__ . '/config/*.config.php'],
            'module' => [
                \Laminas\Cache\Module::class,
                \Laminas\Cache\Storage\Adapter\Filesystem\Module::class,
            ],
            'container_provider' => ServiceManager\LaminasServiceManager::class,
        ]);

        $app->setApplicationType(Application::APP_CONSOLE)->build();
        $command = $app->getApplication()->find('asset');

        $commandTester = new CommandTester($command);
        $commandTester->execute(["--build" => true]);
        $this->assertEquals($commandTester->getStatusCode(), Command::SUCCESS);
    }
}
