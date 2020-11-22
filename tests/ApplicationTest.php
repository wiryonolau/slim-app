<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Application;

final class ApplicationTest extends TestCase {
    public function testService() {
        $app = new Application();
        $serviceFactories = $app->getConfig()["service"]["factories"];

        foreach($serviceFactories as $service => $factory) {
            $this->assertEquals(is_object($app->getContainer()->get($service)), true);
        }
    }
    
}


?>
