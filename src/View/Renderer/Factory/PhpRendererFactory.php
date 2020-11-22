<?php

namespace App\View\Renderer\Factory;

use Slim\Views\PhpRenderer;
use Psr\Container\ContainerInterface;

class PhpRendererFactory {
    public function __invoke(ContainerInterface $container) {
        $template_path = realpath($container->get("Config")->getConfig()["view"]["template_path"]);
        return new PhpRenderer($template_path);
    }
}
