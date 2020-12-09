<?php

namespace App\View\Helper\Factory;

use Psr\Container\ContainerInterface;
use App\View\Helper\CsrfTokenHelper;
use App\Csrf\CsrfTokenManager;

class CsrfTokenHelperFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config")->getConfig();
        $field_name = $config["session"]["csrf_field_name"];
        $tokenManager = $container->get(CsrfTokenManager::class);
        return new CsrfTokenHelper($field_name, $tokenManager);
    }
}
