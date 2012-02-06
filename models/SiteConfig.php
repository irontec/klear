<?php


class Klear_Model_SiteConfig
{
    protected $_year;
    protected $_name;
    protected $_lang;
    protected $_logo;
    protected $_langs = array();

    protected $_authConfig = false;
    
    public function setConfig(Zend_Config $config)
    {
        // TODO: Control de errores, configuración mal seteada
        $this->_year = $config->year;
        $this->_name = $config->sitename;

        if (isset($config->logo)) {
            $this->_logo = $config->logo;
        }

        if (isset($config->langs)) {
            foreach ($config->langs as $_langIden => $lang) {
                $language = new Klear_Model_Language();
                $language->setIden($_langIden);
                $language->setConfig($lang);
                $this->_langs[$language->getIden()] = $language;
            }
        }
        
        $this->_lang = $this->_langs[$config->lang];
        
        if (isset($config->auth)) {
            
            $this->_authConfig = new Klear_Model_KConfigParser();
            $this->_authConfig->setConfig($config->auth);
        }
        
    }

    public function getYear()
    {
        return $this->_year;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getLang()
    {
        return $this->_lang;

    }

    public function getLogo()
    {
        return $this->_logo;

    }

    public function getLangs()
    {
        if (sizeof($this->_langs) == 0) return false;
        return $this->_langs;
    }
    
    
    
    public function getAuthConfig()
    {
        
        return $this->_authConfig;
    
    }
}