<?php

declare(strict_types=1);

namespace Itseasy\Middleware;

use Slim\Routing\RouteContext;
use Slim\Interfaces\RouteInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Log\LoggerAwareTrait;
use Laminas\Log\LoggerAwareInterface;

abstract class AbstractMiddleware implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected function getRoute(ServerRequestInterface $request): ?RouteInterface
    {
        $routeContext = RouteContext::fromRequest($request);
        return $routeContext->getRoute();
    }
}
