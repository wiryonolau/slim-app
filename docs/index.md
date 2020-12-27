# Slim4 Application #

Slim4 framework application wrapper with basic functionality and configurable using array

Component:
- **Application**  
  Application bootstrap. Can handle both http and console
- **Session Middleware**   
  A Symfony base session manager using symfony/session
- **Csrf Middleware**   
  A Csrf middleware using symfony/session
- **AssetManager Middleware**   
  An asset manager to load asset outside public folder, cache using symfony/cache
- **Guard Middleware**   
  Basic Authorization library guarding route with action and HTTP method. Require to implement your own *Identity Provider Class* and *Role Provider Class*
- **View**   
  A View class complete with PhpRenderer and view helper. The class will be injected automatically to all Action during application bootstrap.
- **Console**   
  Console application using symfony/console

All middleware are optionals

## Application ##

### Project Structure ###

```
project
    - asset
    - config
        - action.config.php
        - middleware.config.php
        - route.config.php
        - service.config.php
    - public
        - .htaccess
        - index.php
    - src
        - Action
            - Factory
        - Service
            - Factory
    - view
        - layout
            - layout.phtml
```

### Basic Usage ###
```php
<?php
$app = Itseasy\Application();
$app->setConfigPath([
    __DIR__.'/config/*.config.php'
]);
$app->run();
?>
```

### Routing ###

Routing are define in route.config.php using array, the layout are similar to zend configuration. Example

```php
<?php
return [
    "routes" => [
        "name" => [
            "route" => "/", // Slim Route format
            "method" => ["GET"], // HTTP Method default GET if not specify.
            "options" => [
                "action" => Action\Dashboard\Dashboard::action, // Action Class
                "arguments" => [], // Additional arguments
                "middleware" => "" // Glue middleware to this route
            ],
            "child_routes" => [
                // Repeat routes
            ]
        ]
    ]
];
?>
```
All action need to be specify in the action factories before using it here

### Middleware ###

Middleware are define in the middelware.config.php file. Note that the order is very important. Example

```php
<?php
return [
    "middleware" => [
        "middleware" => [
            Itseasy\Csrf\CsrfMiddleware::class,
            Itseasy\Guard\RouteGuardMiddleware::class,
            Itseasy\Session\SessionMiddleware::class,
            Slim\Middleware\RoutingMiddleware::class,
            Itseasy\Asset\AssetMiddleware::class,
            Slim\Middleware\ErrorMiddleware::class,
        ],
    ]
];
?>
```
Special Middleware
- built in **Itseasy\Session\SessionMiddleware** must be put before **Slim\Middleware\RoutingMiddleware**
- built in **Itseasy\Guard\RouteGuardMiddleware** must be put before **Itseasy\Session\SessionMiddleware**
- built in **Itseasy\Csrf\CsrfMiddleware** must be put before **Itseasy\Session\SessionMiddleware**

All custom middleware need to be specify first in the service factories before using it here

### Dependency Injection ###

Slim App is created with PHP-DI.   
PHP-DI will register all factory definition from this array

|Array|
|---|
|$config["service"]["factories"]|
|$config["console"]["command"]["factories"]|
|$config["view_helper"]["factories"]|
|$config["action"]["factories"]|

Config are register inside PHP-DI by default, and can be retrieve using below code in factory class
```php
<?php
$config = $container->get("Config")->getConfig();
?>
```

### Console Application ###

```php
<?php
$app = Itseasy\Application();
$app->setApplicationType(Itseasy\Application::APP_CONSOLE);
$app->setConfigPath([
    __DIR__.'/config/*.config.php'
]);
$app->run();
?>
```

## Session ##

Session can be setup using a configuration file e.g session.config.php, Example

```php
<?php
return [
    "session" => [
        'class' => Session::class,
        'options' => [
            'name' => 'App',
            'cache_expire' => 0,
            'cookie_samesite' => 'strict'
        ],
        'csrf_field_name' => '_csrf',
        'csrf_token_id' => 'app'
    ]
];
?>
```

The class need to be specify in the service factories before using it here.   
Session options is using symfony/session options, you can check the available options there.

## AssetManager Middleware ##

### Configuration ###

```php
<?php
return [
    "asset" => [
        "resolver_configs" => [
            "paths" => [
            ]
        ],
        "caching" => [
            "class" => ""
        ]
    ],
];
?>
```

## Guard Middleware ##

### Configuration ###

```php
<?php
return [
    "guard" => [
        "identity_provider" => "", // Identity Provider class
        "login_route" => "", // Login route
        "authorization" => [
            "default_role" => "guest", // Default role name
            "whitelist" => [], // Whitelist role
            "role_provider" => "", // Role Provider class
            "roles" => [] // Roles Permission list
        ],
    ]   
];
?>
```

## View ##

### Configuration ###

```php
<?php

return [
    "view" => [
        "class" => View\View::class,
        "renderer" => View\Renderer\PhpRenderer::class,
        "default_layout" => "layout/layout",
        "error_layout" => "layout/error",
        "default_template_suffix" => "phtml",
        "template_path" => __DIR__."/../view"
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
];
?>
```

## Console ##

### Configuration ###

```php
<?php
return [
    "console" => [
        "commands" => [],
        "factories" => []
    ]
];
?>
```
