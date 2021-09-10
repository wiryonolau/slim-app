<?php

namespace Itseasy\Translator;

use Itseasy\Middleware\AbstractMiddleware;
use Laminas\I18n\Translator\TranslatorInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class LocaleMiddleware extends AbstractMiddleware
{
    const LOCALE_QUERY = "lang";

    protected $translator;
    protected $default_locale;

    public function __construct(TranslatorInterface $translator, string $default_locale) {
        $this->translator = $translator;
        $this->default_locale = $default_locale;
    }

    public function __invoke(Request $request, RequestHandler $handler) : Response
    {
        $locale = $this->getLocale($request);
        $this->translator->setLocale($locale);
    }

    private function getLocale(Request $request)
    {
        $locale = $this->request->getQueryParams()[self::LOCALE_QUERY];
        if (empty($locale)) {
            return $this->default_locale;
        }
        return $locale;
    }
}
