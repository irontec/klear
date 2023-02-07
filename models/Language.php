<?php

class Klear_Model_Language implements \Stringable
{
    protected $_iden;
    protected $_title;
    protected $_language;
    protected $_locale;

    protected $_jQLocales = array(
        'af', 'ar', 'ar-DZ', 'az', 'bg', 'bs', 'ca', 'cs', 'cy-GB', 'da', 'de',
        'el', 'en-AU', 'en', 'en-GB', 'en-NZ', 'eo', 'es', 'et', 'eu', 'fa', 'fi',
        'fo', 'fr', 'fr-CH', 'ge', 'gl', 'he', 'hi', 'hr', 'hu', 'hy', 'id',
        'is', 'it', 'ja', 'kk', 'km', 'ko', 'lb', 'lt', 'lv', 'mk', 'ml', 'ms',
        'nl', 'nl-BE', 'no', 'pl', 'pt', 'pt-BR', 'rm', 'ro', 'ru', 'sk', 'sl',
        'sq', 'sr', 'sr-SR', 'sv', 'ta', 'th', 'tj', 'tr', 'uk', 'vi', 'zh-CN',
        'zh-HK', 'zh-TW'
    );

    public function __toString(): string
    {
        return (string) $this->_title;
    }

    public function setConfig(Zend_Config $config)
    {
        $title = $config->get('title');
        if ($title) {
            $this->_setTitle($title);
        } else {
            $this->_setTitle($this->_iden);
        }

        $language = $config->get('language');
        if ($language) {
            $this->_setLanguage($language);
        }

        $locale = $config->get('locale');
        if ($locale) {
            $this->_setLocale($locale);
        }
    }

    protected function _setIden($iden)
    {
        $this->_iden = $iden;
    }

    protected function _setTitle($title)
    {
        $this->_title = $title;
    }

    protected function _setLanguage($language)
    {
        $this->_language = $language;
    }

    protected function _setLocale($locale)
    {
        $this->_locale = $locale;
    }

    public function setIden($iden)
    {
        $this->_setIden($iden);
    }

    public function getIden()
    {
        return $this->_iden;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getLanguage()
    {
        return $this->_language;
    }

    public function getLocale()
    {
        return $this->_locale;
    }

    public function getJqLocale()
    {
        // You will never work, just in case...
        if (isset($this->_jQLocales[$this->_locale])) {
            return $this->_locale;
        }

        $locale = str_replace("_", "-", (string) $this->_locale);

        if (in_array($locale, $this->_jQLocales)) {
            return $locale;
        }

        [$locale, ] = explode("-", $locale, 2);
        if (in_array($locale, $this->_jQLocales)) {
            return $locale;
        }

        return false;
    }

    public function toArray()
    {
        return array(
            'iden' => $this->getIden(),
            'language' => $this->getLanguage(),
            'locale' => $this->getLocale(),
            'title' => $this->getTitle(),
            'jqLocale' => $this->getJqLocale()
        );
    }
}
