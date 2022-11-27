<?php

namespace Itseasy\Translator;

use Laminas\I18n\Translator\Translator as LaminasTranslator;

class Translator extends LaminasTranslator
{
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        $locale = ($locale ?: $this->getLocale());
        $translation = $this->getTranslatedMessage($message, $locale, $textDomain);

        if ($translation !== null && $translation !== '') {
            return $translation;
        }

        if (
            null !== ($fallbackLocale = $this->getFallbackLocale())
            && $locale !== $fallbackLocale
        ) {
            return $this->translate($message, $textDomain, $fallbackLocale);
        }

        return sprintf('%s', $message);
    }
}
