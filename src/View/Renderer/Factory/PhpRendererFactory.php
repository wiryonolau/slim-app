<?php

namespace App\View\Renderer\Factory;

use App\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class PhpRendererFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config");
        $template_path = realpath($config->getConfig()["view"]["template_path"]);

        $renderer = new PhpRenderer($template_path);
        $renderer->setLayout($config->getConfig()["view"]["default_layout"]);

        if (!empty($config->getConfig()["view"]["helpers"])) {
            foreach($config->getConfig()["view"]["helpers"] as $helper) {
                if (!$container->has($helper)) {
                    continue;
                }
                $renderer->addViewHelper($container->get($helper));
            }
        }

        return $renderer;
    }
}

