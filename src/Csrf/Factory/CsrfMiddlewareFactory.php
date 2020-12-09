<?php

namespace App\Csrf\Factory;

use App\Csrf\CsrfMiddleware;
use Psr\Container\ContainerInterface;
use App\Csrf\CsrfTokenManager;
use App\View\Helper\FlashMessageHelper;

class CsrfMiddlewareFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config")->getConfig();
        $field_name = $config["session"]["csrf_field_name"];
        $csrfTokenManager = $container->get(CsrfTokenManager::class);
        $session = $container->get($config["session"]["class"]);
        return new CsrfMiddleware($field_name, $csrfTokenManager, $session);
    }
}
