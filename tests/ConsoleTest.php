<?php

namespace Itseasy\Test;

use Itseasy\Application;
use Itseasy\ServiceManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ConsoleTest extends TestCase
{
    private $app;

    public function setUp(): void
    {
        $this->app = new Application([
            "config_path" => [__DIR__ . '/config/*.config.php', __DIR__ . '/../config/*.config.php'],
            'module' => [
                ModuleTest\Module::class,
                \Laminas\Form\Module::class,
                \Laminas\Cache\Module::class,
                \Laminas\Cache\Storage\Adapter\Filesystem\Module::class
            ],
            'container_provider' => ServiceManager\LaminasServiceManager::class,
        ]);
        $this->app->setApplicationType(Application::APP_CONSOLE)->build();
    }

    public function testConsole()
    {

        $username = "yoyoyo";
        $command = $this->app->getApplication()->find('test');
        $commandTester = new CommandTester($command);
        $commandTester->execute(["username" => $username]);
        $output = $commandTester->getDisplay();
        $this->assertEquals($output, "Hello $username\n");
    }

    public function testAssetConsole()
    {
        $command = $this->app->getApplication()->find('asset');

        $commandTester = new CommandTester($command);
        $commandTester->execute(["--build" => true]);
        $this->assertEquals($commandTester->getStatusCode(), Command::SUCCESS);
    }
}
