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

        if (!empty($config->getConfig()["view_helper"]["aliasses"])) {
            foreach($config->getConfig()["view_helper"]["aliasses"] as $alias => $helper) {
                $alias = is_int($alias) ? "" : $alias;
                $renderer->addViewHelper($container->get($helper), $alias);
            }
        }

        return $renderer;
    }
}
