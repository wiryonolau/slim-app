<?php

declare(strict_types=1);

namespace Itseasy\Csrf\Factory;

use Psr\Container\ContainerInterface;
use Itseasy\Session;
use Itseasy\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;

class CsrfTokenManagerFactory
{
    public function __invoke(ContainerInterface $container): CsrfTokenManager
    {
        $config = $container->get("Config")->getConfig();
        $token_id = $config["session"]["csrf_token_id"];

        $sessionClass = $container->get(Session::class);
        $generator = new UriSafeTokenGenerator();
        $storage = new SessionTokenStorage($sessionClass);
        $tokenManager = new CsrfTokenManager($token_id, $generator, $storage);

        return $tokenManager;
    }
}
