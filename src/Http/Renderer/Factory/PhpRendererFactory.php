<?php

namespace App\Http\Renderer\Factory;

use Psr\Container\ContainerInterface;

class PhpRendererFactory {
    public function __invoke(ContainerInterface $container) {
        $template_path = realpath($container->get("Config")["view"]["template_path"]);
        return new PhpRenderer($template_path);
    }
}
