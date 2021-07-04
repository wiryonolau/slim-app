<?php
namespace Itseasy\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Application;
use Itseasy\Translator;

final class TranslationTest extends TestCase
{
    public function testDI()
    {
        $app = new Application([
            "config_path" => [__DIR__."/config/*.config.php"]
        ]);
        $app->build();

        $translator = $app->getApplication()->getContainer()->get(Translator::class);
        $test = $translator->translate("project", "default", "id_ID");
        $this->assertEquals($test, "proyek");
    }
}
