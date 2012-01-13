<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_KConfigParser {
	
	protected $_config;
	
	
	public function setConfig(Zend_Config $config) {
		$this->_config = $config;
	}
	
	
	/**
	 * @param string$attribute
	 * @throws Zend_Exception
	 * @return array   
	 */
	public function getPropertyML($attribute, $fieldName = false, $required = false) {
		
		if (!isset($this->_config->{$attribute})) {
			if ($required) {
				//TODO: Capturar esta excepción... sino, se romperá todo (la vista no tendrá SiteConfig);
				throw new Zend_Exception("Propiedad ".$attribute." no encontrada.");
			} else {
				// Si no es un campo required, devuelvo false;
				return array($attribute,false);
			}
		}
		
		if (false === $fieldName) {
			$fieldName = $attribute;
		}
		
		$attributeValue = '_' . $fieldName;
		
		if (is_string($this->_config->{$attribute})) {
				
			return array($attributeValue,$this->_config->{$attribute});
		}
		
		/*
		 * El atributo tiene multi-idioma
		*/
		$attributeValue .= "_i18n";
		
		if ( (is_object($this->_config->{$attribute})) && (isset($this->_config->{$attribute}->i18n)) ) {
		
			$retArr = array();
			
			foreach ($this->_config->{$attribute}->i18n as $lang => $_data) {
				$retArr[$lang] = $_data;
			}
			
		} else {
			
			Throw new Zend_Exception("Formato de configuración no soportada");
			
		}
		
		return array($attributeValue,$retArr);
	}
	
	
	public function getProperty($attribute,$required) {
		
		if (!isset($this->_config->{$attribute})) {
			if ($required) {
				Throw new Zend_Exception("Propiedad ".$attribute." no encontrada.");
			}
			return null;
		}
		
		return $this->_config->{$attribute};
	
	}
	
	public function getRaw() {
		return $this->_config;
	}
	
	public function exists($path) {
		$_segments = explode("->",$path);
		$ref = $this->_config;
		
		foreach($_segments as $_segment) {
			if (!isset($ref->$_segment)) return false;
			$ref = $ref->$_segment;
		}
		
		return true;
	}
	
}
	
