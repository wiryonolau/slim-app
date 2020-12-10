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
            Csrf\CsrfTokenManager::class => Csrf\Factory\CsrfTokenManagerFactory::class,
            Csrf\CsrfMiddleware::class => Csrf\Factory\CsrfMiddlewareFactory::class,
            Session::class => Session\Factory\SessionFactory::class,
        ]
    ],
    "session" => [
        'class' => Session::class,
        'options' => [
            'name' => 'App',
            'cache_expire' => 0,
            'cookie_samesite' => 'strict'
        ],
        'csrf_field_name' => '_csrf',
        'csrf_token_id' => 'app'
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
        "class" => View\View::class,
        "renderer" => View\Renderer\PhpRenderer::class,
        "default_layout" => "layout/layout",
        "default_template_suffix" => "phtml",
        "template_path" => __DIR__."/../view",
    ],
    "view_helpers" => [
        "aliases" => [
            "url" => View\Helper\UrlHelper::class,
            "flash" => View\Helper\FlashMessageHelper::class,
            "csrf" => View\Helper\CsrfTokenHelper::class,
        ],
        "factories" => [
            View\Helper\FlashMessageHelper::class => View\Helper\Factory\FlashMessageHelperFactory::class,
            View\Helper\UrlHelper::class => View\Helper\Factory\UrlHelperFactory::class,
            View\Helper\CsrfTokenHelper::class => View\Helper\Factory\CsrfTokenHelperFactory::class
        ]
    ],
    "console" => [
        "commands" => [],
        "factories" => []
    ]
];
