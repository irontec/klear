<?php


class Klear_Model_Language
{
    protected $_iden;
    protected $_title;
    protected $_language;
    protected $_locale;

    protected $_jQLocales = array('af','ar','ar-DZ','az','bg','bs','ca','cs','cy-GB','da','de','el','en-AU','en-GB','en-NZ','eo','es','et','eu','fa','fi','fo','fr','fr-CH','ge','gl','he','hi','hr','hu','hy','id','is','it','ja','kk','km','ko','lb','lt','lv','mk','ml','ms','nl','nl-BE','no','pl','pt','pt-BR','rm','ro','ru','sk','sl','sq','sr','sr-SR','sv','ta','th','tj','tr','uk','vi','zh-CN','zh-HK','zh-TW');
    
    
    public function __toString()
    {
        return $this->_title;
    }

    public function setConfig(Zend_Config $config)
    {
        if (null != ($title = $config->get('title'))) {
            $this->_setTitle($title);
        } else {
            $this->_setTitle($this->_iden);
        }

        if (null != ($language = $config->get('language'))) {
            $this->_setLanguage($language);
        }

        if (null != ($locale = $config->get('locale'))) {
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
    
    public function getjQLocale()
    {
        // You will never work, just in case...
        if (isset($this->_jQLocales[$this->_locale])) {
            return $this->_locale;
        }
    
        $locale = str_replace("_", "-", $this->_locale);
        
        if (in_array($locale,$this->_jQLocales)) {
            return $locale;
        }
    
        list($locale,) = explode("-",$locale,2);
        if (in_array($locale,$this->_jQLocales)) {
            return $locale;
        }
    
        return false;
    
    }


}
