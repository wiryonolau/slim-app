<?php

namespace Itseasy\Test;

use Itseasy\ServiceManager;
use Itseasy\Application;
use Itseasy\Guard\RouteGuardMiddleware;
use PHPUnit\Framework\TestCase;

final class GuardTest extends TestCase
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
        $this->app->build();
    }


    public function testGuardWithConfig()
    {
        $routeGuardMiddleware = $this->app->getContainer()->get(RouteGuardMiddleware::class);

        $routeGuard = $routeGuardMiddleware->getRouteGuard();

        $this->assertEquals($routeGuard->allow('GET', Action\TestAction::class), true);
        $this->assertEquals($routeGuard->allow('POST', Action\TestAction::class), false);
    }
}
