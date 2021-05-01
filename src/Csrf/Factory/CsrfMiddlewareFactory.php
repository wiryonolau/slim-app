<?php
declare(strict_types = 1);

namespace Itseasy\Csrf\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Csrf\CsrfMiddleware;
use Itseasy\Csrf\CsrfTokenManager;
use Itseasy\View\Helper\FlashMessageHelper;

class CsrfMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : CsrfMiddleware
    {
        $config = $container->get("Config")->getConfig();
        $field_name = $config["session"]["csrf_field_name"];
        $csrfTokenManager = $container->get(CsrfTokenManager::class);
        $session = $container->get($config["session"]["class"]);
        return new CsrfMiddleware($field_name, $csrfTokenManager, $session);
    }
}
