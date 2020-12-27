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

Check docs folder for documentation
