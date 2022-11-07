<?php

namespace Itseasy\Test;

use Exception;
use Itseasy\Application;
use Itseasy\ServiceManager;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    // Dont test php8 libs on php7
    const PHP8_LIBS = [
        'Laminas\Form\Annotation\AttributeBuilder',
        'FormAttributeBuilder',
    ];

    public function testDIContainer()
    {
        $app = new Application([
            'config_path' => [__DIR__ . '/config/*.config.php'],
            'module' => [
                ModuleTest\Module::class,
                \Laminas\Form\Module::class,
                \Laminas\Cache\Module::class,
                \Laminas\Cache\Storage\Adapter\Filesystem\Module::class
            ],
            'container_provider' => ServiceManager\DIServiceManager::class,
        ]);

        $app->build();

        $entries = $app->getContainer()->getKnownEntryNames();
        foreach ($entries as $entry) {
            if (PHP_MAJOR_VERSION < 8 and in_array($entry, self::PHP8_LIBS)) {
                continue;
            }

            $object = null;
            try {
                $object = $app->getContainer()->get($entry);
            } catch (\DI\NotFoundException $e) {
                debug(sprintf("\nService : %s\n", $entry));
                debug(sprintf("ERROR : \n%s\n", $e->getMessage()));
            } catch (Exception $ex) {
                debug($ex->getMessage());
            }
            $this->assertEquals(is_object($object), true);
        }

        // Test abstract factories
        $a = $app->getContainer()->get('yoyo');
        $b = $app->getContainer()->get('yoyo');
        $this->assertEquals($a, $b);

        $this->assertEquals($app->getContainer()->get('testalias') instanceof  Provider\IdentityProvider, true);
    }

    public function testLaminasContainer()
    {
        $app = new Application([
            'config_path' => [__DIR__ . '/config/*.config.php'],
            'module' => [
                ModuleTest\Module::class,
                \Laminas\Form\Module::class,
                \Laminas\Cache\Module::class,
                \Laminas\Cache\Storage\Adapter\Filesystem\Module::class
            ],
            'container_provider' => ServiceManager\LaminasServiceManager::class,
        ]);

        $app->build();

        $entries = array_merge(
            array_keys($app->getConfig()['service']['factories']),
            array_keys($app->getConfig()['service']['aliases']),
            array_keys($app->getConfig()['view_helpers']['factories']),
        );

        foreach ($entries as $entry) {
            if (PHP_MAJOR_VERSION < 8 and in_array($entry, self::PHP8_LIBS)) {
                continue;
            }

            $object = null;
            try {
                $object = $app->getContainer()->get($entry);
            } catch (\DI\NotFoundException $e) {
                debug(sprintf("\nService : %s\n", $entry));
                debug(sprintf("ERROR : \n%s\n", $e->getMessage()));
            } catch (Exception $ex) {
                debug($ex->getMessage());
            }
            $this->assertEquals(is_object($object), true);
        }
    }
}
