<?php

namespace Itseasy\Test;

use Itseasy\ServiceManager;
use PHPUnit\Framework\TestCase;
use Itseasy\Application;
use Itseasy\Translator;

final class TranslationTest extends TestCase
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


    public function testTranslation()
    {
        $translator = $this->app->getApplication()->getContainer()->get(Translator::class);

        $this->assertEquals($translator->translate("project", "default", "id_ID"), "proyek");
        $this->assertEquals($translator->translate("notexist", "default", "id_ID"), "notexist");
    }
}
