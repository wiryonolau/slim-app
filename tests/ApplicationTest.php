<?php
namespace Itseasy\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Application;
use Exception;

final class ApplicationTest extends TestCase
{
    public function testDI()
    {
        $app = new Application([
            "config_path" => [__DIR__."/config/*.config.php"],
            "module" => [
                ModuleTest\Module::class
            ]
        ]);

        $app->build();

        $entries = $app->getContainer()->getKnownEntryNames();
        foreach ($entries as $entry) {
            try {
                $object = $app->getApplication()->getContainer()->get($entry);
            } catch (Exception $e) {
                debug(sprintf("\nService : %s\n", $entry));
                debug(sprintf("ERROR : \n%s\n\n", $e->getMessage()));
                $object = null;
            }


            $this->assertEquals(is_object($object), true);
        }
    }
}
