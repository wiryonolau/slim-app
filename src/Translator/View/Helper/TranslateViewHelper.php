<?php

namespace Itseasy\Translator\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\I18n\View\Helper\AbstractTranslatorHelper;
use Laminas\I18n\View\Helper\Translate;

class TranslateViewHelper
{
    /**
     * Wrapper class for Laminas\I18n\View\Helper\Translate
     * Slim doesn't have mechanism to inject Translator to
     * view helper Lamainas\I18n\Translator\HelperConfig
     * A manual injection is require here.
     *
     * To use Helper  Lamainas\I18n\Translator\HelperConfig directly,
     * it must be call from Renderer and have Translate set for each helper
     * that extends Laminas\I18n\View\Helper\AbstractTranslatorHelper
     */
    public function __invoke(ContainerInterface $container) : AbstractTranslatorHelper
    {
        $translator = $container->get(Translator::class);

        $translate = new Translate();
        $translate->setTranslator($translator);
        return $translate;
    }
}
