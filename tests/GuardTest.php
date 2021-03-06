<?php
namespace Itseasy\Test;

use DI;
use PHPUnit\Framework\TestCase;
use Itseasy\Application;
use Itseasy\Guard\RouteGuardMiddleware;

final class GuardTest extends TestCase
{
    public function testGuardWithConfig()
    {
        $app = new Application([
            "config_path" => __DIR__."/config/*.config.php"
        ]);
        $app->build();

        $routeGuardMiddleware = $app->getContainer()->get(RouteGuardMiddleware::class);

        $routeGuard = $routeGuardMiddleware->getRouteGuard();

        $this->assertEquals($routeGuard->allow("GET", Action\TestAction::class), true);
        $this->assertEquals($routeGuard->allow("POST", Action\TestAction::class), false);
    }
}
