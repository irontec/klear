<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_KConfigParser
{
    protected $_config;

    public function setConfig(Zend_Config $config)
    {
        $this->_config = $config;
    }

    /**
     * @param string$attribute
     * @throws Zend_Exception
     * @return array
     */
    public function getPropertyML($attribute, $fieldName = false, $required = false)
    {
        Throw new Zend_Exception("Deprecated Method getPropertyML");
    }

    public function getProperty($attribute, $required = false, $lang = null)
    {
        if (!isset($this->_config->{$attribute})) {
            if ($required) {
                Throw new Zend_Exception("Propiedad ".$attribute." no encontrada.");
            }
            return null;
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

    public function exists($path)
    {
        $_segments = explode("->", $path);
        $ref = $this->_config;

        foreach ($_segments as $_segment) {
            if (!isset($ref->$_segment)) return false;
            $ref = $ref->$_segment;
        }

        return true;
    }
}
