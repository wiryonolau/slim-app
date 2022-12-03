<?php

declare(strict_types=1);

namespace Itseasy\View\Helper\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\View\Helper\UrlHelper;

class UrlHelperFactory
{
    public function __invoke(ContainerInterface $container): UrlHelper
    {
        return new UrlHelper();
    }
}
