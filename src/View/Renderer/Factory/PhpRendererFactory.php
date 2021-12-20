<?php
declare(strict_types = 1);

namespace Itseasy\View\Renderer\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\View\Renderer\PhpRenderer;
use DI;

class PhpRendererFactory
{
    public function __invoke(ContainerInterface $container) : PhpRenderer
    {
        $config = $container->get("Config")->getConfig();
        $viewConfig = $config["view"];
        $viewHelperConfig = $config["view_helpers"];

        $template_path = $viewConfig["template_path"];

        $renderer = new PhpRenderer($template_path);
        $renderer->setLayout($viewConfig["default_layout"]);
        $renderer->setTemplateSuffix($viewConfig["default_template_suffix"]);

        if (!empty($viewHelperConfig["factories"])) {
            foreach ($viewHelperConfig["factories"] as $view => $factory) {
                if (is_object($factory)) {
                    $container->set($view, $factory);
                } else {
                    $container->set($view, DI\factory($factory));
                }
            }
        }

        if (!empty($viewHelperConfig["aliases"])) {
            foreach ($viewHelperConfig["aliases"] as $alias => $helper) {
                $helper = $container->get($helper);
                $renderer->addViewHelper($helper, $alias);
            }
        }

        return $renderer;
    }
}
