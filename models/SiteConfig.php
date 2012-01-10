<?php


class Klear_Model_SiteConfig {
	
	protected $_year;
	protected $_name;
	protected $_lang;
	protected $_logo;
	protected $_langs = array();
	
	public function setConfig(Zend_Config $config) {
		// TO-DO COntrol de errores, configuración mal seteada
		$this->_year = $config->year;
		$this->_name = $config->sitename;
		$this->_lang = $config->lang;
		
		if (isset($config->logo)) {
		    $this->_logo = $config->logo;
		}
		
		if (isset($config->langs)) {
		    foreach($config->langs as $_langIden => $lang) {
	            $this->_langs[$_langIden] = $lang;	        
		    }
		    
		}
		
	}
	
	public function getYear() {
		return $this->_year;
	}
	
	public function getName() {
		return $this->_name;
	}
	
    public function getLang() {
		return $this->_lang;
		
	}
	
    public function getLogo() {
		return $this->_logo;
		
	}
	
    public function getLangs() {
		if (sizeof($this->_langs) == 0) return false;
		return $this->_langs;		
	}
	
}