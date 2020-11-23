<?php

namespace App\View\Helper\Factory;

use Psr\Container\ContainerInterface;
use App\View\Helper\UrlHelper;

class UrlHelperFactory {
    public function __invoke(ContainerInterface $container) {
        return new UrlHelper;
    }
}
