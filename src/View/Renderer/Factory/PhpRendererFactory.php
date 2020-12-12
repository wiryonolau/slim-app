<?php

namespace App\View\Renderer\Factory;

use DI;
use App\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class PhpRendererFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config")->getConfig();
        $viewConfig = $config["view"];
        $viewHelperConfig = $config["view_helpers"];

        $template_path = $viewConfig["template_path"];

        $renderer = new PhpRenderer($template_path);
        $renderer->setLayout($viewConfig["default_layout"]);
        $renderer->setTemplateSuffix($viewConfig["default_template_suffix"]);

        if (!empty($viewHelperConfig["factories"])) {
            foreach ($viewHelperConfig["factories"] as $view => $factory) {
                if (is_object($class)) {
                    $container->set($view, $factory);
                } else {
                    $container->set($view, DI\factory($factory));
                }
            }
        }

        if (!empty($viewHelperConfig["aliases"])) {
            foreach($viewHelperConfig["aliases"] as $alias => $helper) {
                $renderer->addViewHelper($container->get($helper), $alias);
            }
        }

        return $renderer;
    }
}
