<?php

namespace Itseasy\Test;

use Exception;
use Itseasy\Application;
use Itseasy\DIContainer;
use Itseasy\LaminasContainer;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    public function testDIContainer()
    {
        $app = new Application([
            'config_path' => [__DIR__.'/config/*.config.php'],
            'module' => [
                ModuleTest\Module::class,
                \Laminas\Form\Module::class,
                \Laminas\Cache\Module::class,
            ],
            'container_provider' => DIContainer::class,
        ]);

        $skip_php8 = [
            'Laminas\Form\Annotation\AttributeBuilder',
            'FormAttributeBuilder',
        ];

        $app->build();

        $entries = $app->getContainer()->getKnownEntryNames();
        foreach ($entries as $entry) {
            if (in_array($entry, $skip_php8)) {
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
            'config_path' => [__DIR__.'/config/*.config.php'],
            'module' => [
                ModuleTest\Module::class,
                \Laminas\Form\Module::class,
                \Laminas\Cache\Module::class,
            ],
            'container_provider' => LaminasContainer::class,
        ]);

        $skip_php8 = [
            'Laminas\Form\Annotation\AttributeBuilder',
            'FormAttributeBuilder',
        ];

        $app->build();

        $entries = array_merge(
            array_keys($app->getConfig()['service']['factories']),
            array_keys($app->getConfig()['service']['aliases']),
            array_keys($app->getConfig()['view_helpers']['factories']),
        );

        debug($entries);

        foreach ($entries as $entry) {
            if (in_array($entry, $skip_php8)) {
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
