<?php
declare(strict_types = 1);

namespace Itseasy\View\Helper\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\View\Helper\CsrfTokenHelper;
use Itseasy\Csrf\CsrfTokenManager;

class CsrfTokenHelperFactory
{
    public function __invoke(ContainerInterface $container) : CsrfTokenHelper
    {
        $config = $container->get("Config")->getConfig();
        $field_name = $config["session"]["csrf_field_name"];
        $tokenManager = $container->get(CsrfTokenManager::class);
        return new CsrfTokenHelper($field_name, $tokenManager);
    }
}
