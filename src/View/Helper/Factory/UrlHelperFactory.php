<?php

namespace Itseasy\View\Helper\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\View\Helper\UrlHelper;

class UrlHelperFactory {
    public function __invoke(ContainerInterface $container) {
        return new UrlHelper();
    }
}
