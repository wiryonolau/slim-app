<?php

namespace Itseasy\Test;

use PHPUnit\Framework\TestCase;
use Itseasy\Application;
use Itseasy\Translator;
use Itseasy\ServiceManager;

final class TranslationTest extends TestCase
{
    public function testTranslation()
    {
        $app = new Application([
            'config_path' => [__DIR__ . '/config/*.config.php'],
            'module' => [
                \Laminas\Cache\Module::class,
                \Laminas\Cache\Storage\Adapter\Filesystem\Module::class,
            ],
            'container_provider' => ServiceManager\LaminasServiceManager::class,
        ]);
        $app->build();

        $translator = $app->getApplication()->getContainer()->get(Translator::class);

        $this->assertEquals($translator->translate("project", "default", "id_ID"), "proyek");
        $this->assertEquals($translator->translate("notexist", "default", "id_ID"), "notexist");
    }
}
