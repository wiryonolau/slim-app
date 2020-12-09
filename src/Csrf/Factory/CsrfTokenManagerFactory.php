<?php

namespace App\Csrf\Factory;

use App\Session;
use Psr\Container\ContainerInterface;
use App\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;

class CsrfTokenManagerFactory {
    public function __invoke(ContainerInterface $container) {
        $config = $container->get("Config")->getConfig();
        $token_id = $config["session"]["csrf_token_id"];

        $sessionClass = $container->get(Session::class);
        $generator = new UriSafeTokenGenerator();
        $storage = new SessionTokenStorage($sessionClass);
        $tokenManager = new CsrfTokenManager($token_id, $generator, $storage);

        return $tokenManager;
    }
}
