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

    public function getProperty($attribute, $required = false, $lang = false)
    {
        if (!isset($this->_config->{$attribute})) {
            if ($required) {
                Throw new Zend_Exception("Propiedad ".$attribute." no encontrada.");
            }
            return null;
        }

        $currentSystemLanguage = Zend_Registry::get('currentSystemLanguage');
        
        
        $_system_lang = $currentSystemLanguage->getLanguage(); // TO-DO: Recoger el idioma del Zend Registry?
        if (false === $lang) {
            $lang = $_system_lang;
        }

        /*
         * El atributo tiene multi-idioma
        */
        if ( (is_object($this->_config->{$attribute})) && (isset($this->_config->{$attribute}->i18n->{$lang})) ) {

            if (isset($this->_config->{$attribute}->i18n->{$lang})) {
                return $this->_config->{$attribute}->i18n->{$lang};
            }
            //Si no tenemos el idioma deseado en el array, devolvemos el primer idioma
            foreach ($this->_config->{$attribute}->i18n as $lang => $_data) {
                return $_data;
            }
        }
        if ( (is_object($this->_config->{$attribute})) && !(isset($this->_config->{$attribute}->i18n->{$lang})) ) {
            $lang = Zend_Registry::get('SystemDefaultLanguage')->getLanguage();
            if (isset($this->_config->{$attribute}->i18n->{$lang})) {
                return $this->_config->{$attribute}->i18n->{$lang};
            } else {
                foreach (Zend_Registry::get('SystemLanguages') as $lang) {
                    if (isset($this->_config->{$attribute}->i18n->{$lang->getLanguage()})) {
                        return $this->_config->{$attribute}->i18n->{$lang->getLanguage()};
                    }
                }
            }
        }
        
        return $this->_config->{$attribute};
    }

    public function getRaw()
    {
        return $this->_config;
    }

    public function exists($path)
    {
        $_segments = explode("->",$path);
        $ref = $this->_config;

        foreach($_segments as $_segment) {
            if (!isset($ref->$_segment)) return false;
            $ref = $ref->$_segment;
        }

        return true;
    }
}

