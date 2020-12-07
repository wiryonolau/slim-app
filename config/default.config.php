<?php

namespace App;

return [
    "service" => [
        "factories" => [
            Session\SessionMiddleware::class => Session\Factory\SessionMiddlewareFactory::class,
            Asset\AssetMiddleware::class => Asset\Factory\AssetMiddlewareFactory::class,
            Guard\ArrayRoleProvider::class => Guard\Factory\ArrayRoleProviderFactory::class,
            Guard\GuardOption::class => Guard\Factory\GuardOptionFactory::class,
            Guard\RouteGuard::class => Guard\Factory\RouteGuardFactory::class,
            Guard\RouteGuardMiddleware::class => Guard\Factory\RouteGuardMiddlewareFactory::class,
            View\Renderer\PhpRenderer::class => View\Renderer\Factory\PhpRendererFactory::class,
            Session::class => Session\Factory\SessionFactory::class,
        ]
    ],
    "session" => [
        'name' => 'App',
        'cache_expire' => 0
    ],
    "asset" => [
        "resolver_configs" => [
            "paths" => [
            ]
        ],
    ],
    "guard" => [
        "identity_provider" => "",
        "login_route" => "",
        "authorization" => [
            "default_role" => "guest",
            "whitelist" => [],
            "role_provider" => "",
            "roles" => []
        ],
    ],
    "view" => [
        "view" => View\View::class,
        "renderer" => View\Renderer\PhpRenderer::class,
        "default_layout" => "layout/layout",
        "default_template_suffix" => "phtml",
        "template_path" => __DIR__."/../view",
    ],
    "view_helpers" => [
        "aliases" => [
            "url" => View\Helper\UrlHelper::class,
            "flash" => View\Helper\FlashMessageHelper::class
        ],
        "factories" => [
            View\Helper\FlashMessageHelper::class => View\Helper\Factory\FlashMessageHelperFactory::class,
            View\Helper\UrlHelper::class => View\Helper\Factory\UrlHelperFactory::class
        ]
    ]
];
