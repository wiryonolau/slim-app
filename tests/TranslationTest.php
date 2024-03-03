<?php

namespace Itseasy\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Application;
use Itseasy\Translator\Translator;

final class TranslationTest extends TestCase
{
    public function testDI()
    {
        $app = new Application([
            "config_path" => [__DIR__ . "/config/*.config.php"]
        ]);
        $app->build();

        $translator = $app->getApplication()->getContainer()->get(Translator::class);

        $this->assertEquals($translator->translate("project", "default", "id_ID"), "proyek");
        $this->assertEquals($translator->translate("notexist", "default", "id_ID"), "{notexist}");
    }
}
