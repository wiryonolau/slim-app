<?php

namespace App\Csrf\Factory;

use App\Csrf\CsrfMiddleware;
use Psr\Container\ContainerInterface;
use App\Csrf\CsrfTokenManager;

class CsrfMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config")->getConfig();
        $field_name = $config["session"]["csrf_field_name"];
        return new CsrfMiddleware($field_name, $container->get(CsrfTokenManager::class));
    }
}
