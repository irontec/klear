<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_ConfigParser
{
    protected $_config;

    public function setConfig(Zend_Config $config)
    {
        $this->_config = $config;
    }

    public function getRequiredProperty($attribute, $lang = null)
    {
        if (!isset($this->_config->{$attribute})) {
            throw new Zend_Exception("Propiedad " . $attribute . " no encontrada.");
        }

        return $this->_getProperty($attribute, $lang);
    }

    public function getProperty($attribute, $lang = null)
    {
        if (!isset($this->_config->{$attribute})) {
            return null;
        }

        return $this->_getProperty($attribute, $lang);
    }

    protected function _getProperty($attribute, $lang = null)
    {
        if (!is_null($lang) && !is_string($lang)) {
            throw new \InvalidArgumentException('$lang attribute type is not correct. String expected');
        }

        $configAttribute = $this->_config->{$attribute};
        if ($this->_isMultilangProperty($configAttribute)) {
            return $this->_getMultilangProperty($configAttribute, $lang);
        }
        return $configAttribute;
    }

    protected function _isMultilangProperty($attribute)
    {
        return is_object($attribute);
    }

    protected function _getMultilangProperty($configAttribute, $lang = null)
    {
        if (is_null($lang)) {
            $lang = Zend_Registry::get('currentSystemLanguage')->getLanguage();
        }

        if ((isset($configAttribute->i18n->{$lang}))) {
            return $configAttribute->i18n->{$lang};
        }

        $defaultLang = Zend_Registry::get('SystemDefaultLanguage')->getLanguage();
        if (isset($configAttribute->i18n->{$defaultLang})) {
            return $configAttribute->i18n->{$defaultLang};
        }

        $allLanguages = Zend_Registry::get('SystemLanguages');
        foreach ($allLanguages as $currentLanguage) {
            if (isset($configAttribute->i18n->{$currentLanguage->getLanguage()})) {
                return $configAttribute->i18n->{$currentLanguage->getLanguage()};
            }
        }

        return $configAttribute;
    }

    public function getRaw()
    {
        return $this->_config;
    }

    /**
     * Checks if given path exists in config
     * @param unknown_type $path
     * @return boolean
     */
    public function exists($path)
    {
        $_segments = explode("->", $path);
        $ref = $this->_config;

        foreach ($_segments as $_segment) {
            if (!isset($ref->$_segment)) {
                return false;
            }
            $ref = $ref->$_segment;
        }

        return true;
    }
}
