<?php

namespace App\View\Helper\Factory;

use Psr\Container\ContainerInterface;
use App\View\Helper\FlashMessageHelper;
use App\Session;

class FlashMessageHelperFactory {
    public function __invoke(ContainerInterface $container) {
        $session = $container->get(Session::class);
        return new FlashMessageHelper($session);
    }
}
