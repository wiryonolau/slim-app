<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Application;

final class ApplicationTest extends TestCase {
    public function testService() {
        $app = new Application(__DIR__."/config/*.config.php");
        $serviceFactories = $app->getConfig()["service"]["factories"];

        foreach($serviceFactories as $service => $factory) {
            try {
                $object = $app->getContainer()->get($service);
            } catch (\Exception $e) {
                fwrite(STDERR, sprintf("\nService : %s\n", $service));
                fwrite(STDERR, sprintf("ERROR : \n%s\n\n", $e->getMessage()));
                $object = null;
            }
            $this->assertEquals(is_object($object), true);

        }
    }

}


?>
