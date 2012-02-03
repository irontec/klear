<?php


class Klear_Model_Language {
    
    protected $_iden;
    
    protected $_title;
    
    protected $_language;
    
    protected $_locale;
    
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
    
    
}
