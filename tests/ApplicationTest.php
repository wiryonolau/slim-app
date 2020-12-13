<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Application;

final class ApplicationTest extends TestCase {
    public function testDI() {
        $app = new Application([
            "config_path" => __DIR__."/config/*.config.php"
        ]);
        $app->build();
        
        $entries = $app->getContainer()->getKnownEntryNames();
        foreach($entries as $entry) {
            try {
                $object = $app->getContainer()->get($entry);
            } catch (\Exception $e) {
                fwrite(STDERR, sprintf("\nService : %s\n", $entry));
                fwrite(STDERR, sprintf("ERROR : \n%s\n\n", $e->getMessage()));
                $object = null;
            }
            $this->assertEquals(is_object($object), true);
        }
    }

}


?>
