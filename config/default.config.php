<?php

namespace Itseasy;

return [
    'service' => [
        'factories' => [
            Asset\AssetManager::class => Asset\Factory\AssetManagerFactory::class,
            Asset\AssetMiddleware::class => Asset\Factory\AssetMiddlewareFactory::class,
            Csrf\CsrfMiddleware::class => Csrf\Factory\CsrfMiddlewareFactory::class,
            Csrf\CsrfTokenManager::class => Csrf\Factory\CsrfTokenManagerFactory::class,
            Guard\ArrayRoleProvider::class => Guard\Factory\ArrayRoleProviderFactory::class,
            Guard\GuardOption::class => Guard\Factory\GuardOptionFactory::class,
            Guard\RouteGuard::class => Guard\Factory\RouteGuardFactory::class,
            Guard\RouteGuardMiddleware::class => Guard\Factory\RouteGuardMiddlewareFactory::class,
            Http\HttpExceptionMiddleware::class => Http\Factory\HttpExceptionMiddlewareFactory::class,
            Navigation\Navigation::class => Navigation\Factory\NavigationFactory::class,
            Navigation\NavigationMiddleware::class => Navigation\Factory\NavigationMiddlewareFactory::class,
            Session::class => Session\Factory\SessionFactory::class,
            Session\SessionMiddleware::class => Session\Factory\SessionMiddlewareFactory::class,
            Translator::class => Translator\Factory\TranslatorFactory::class,
            View\Renderer\PhpRenderer::class => View\Renderer\Factory\PhpRendererFactory::class,
        ],
    ],
    'event' => [
        'listener_aggregate' => [
        ],
    ],
    'session' => [
        'class' => Session::class,
        'options' => [
            'cache_expire' => 0,
            'cookie_samesite' => 'lax',
        ],
        'csrf_field_name' => '_csrf',
        'csrf_token_id' => 'app',
    ],
    'translator' => [
    ],
    'asset' => [
        'resolver_configs' => [
            'paths' => [
            ],
        ],
        'caching' => [
            'class' => '',
            'namespace' => 'asset',
            'ttl' => 3600,
            'path' => '',
        ],
    ],
    'navigation' => [
        'default' => [],
    ],
    'guard' => [
        'identity_provider' => '',
        'login_route' => '',
        'use_redirect' => true,
        'authorization' => [
            'default_role' => 'guest',
            'whitelist' => [],
            'role_provider' => '',
            'roles' => [],
        ],
    ],
    'view' => [
        'class' => View\View::class,
        'renderer' => View\Renderer\PhpRenderer::class,
        'default_layout' => 'layout/layout',
        'error_layout' => 'layout/error',
        'default_template_suffix' => 'phtml',
        'template_path' => __DIR__.'/../view',
    ],
    'view_helpers' => [
        'aliases' => [
            'url' => View\Helper\UrlHelper::class,
            'flash' => View\Helper\FlashMessageHelper::class,
            'csrf' => Csrf\View\Helper\CsrfTokenHelper::class,
            '_' => Translator\View\Helper\TranslateViewHelper::class,
            'translate' => Translator\View\Helper\TranslateViewHelper::class,
            'translatePlural' => Translator\View\Helper\TranslatePluralViewHelper::class,
        ],
        'factories' => [
            View\Helper\FlashMessageHelper::class => View\Helper\Factory\FlashMessageHelperFactory::class,
            View\Helper\UrlHelper::class => View\Helper\Factory\UrlHelperFactory::class,
            Csrf\View\Helper\CsrfTokenHelper::class => Csrf\View\Helper\Factory\CsrfTokenHelperFactory::class,
            Translator\View\Helper\TranslateViewHelper::class => Translator\View\Helper\Factory\TranslateViewHelperFactory::class,
            Translator\View\Helper\TranslatePluralViewHelper::class => Translator\View\Helper\Factory\TranslatePluralViewHelperFactory::class,
        ],
    ],
    'console' => [
        'commands' => [
            Asset\Console\Command\AssetCommand::class,
        ],
        'factories' => [
            Asset\Console\Command\AssetCommand::class => Asset\Console\Command\Factory\AssetCommandFactory::class,
        ],
    ],
];
