<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_Section  implements Iterator {
	
	protected $_name;
	protected $_name_i18n = array();
	
	protected $_description;
	protected $_description_i18n = array();
	
	protected $_menu = null;
	
	protected $_subsections;
	protected $_position = 0;
	
	public function setName($name) {
		$this->_name = $name;
		return $this;
	}
	
	public function setParentMenu($menu) {
	    $this->_menu = $menu;
	    return $this;
	}
	
	
	public function setData(Zend_Config $data) {
		
		$config = new Klear_Model_KConfigParser();
		$config->setConfig($data);
		
		
		list($attrName,$value) = $config->getPropertyML("title","name",true);
		$this->$attrName = $value;
		
		list($attrName,$value) = $config->getPropertyML("description",false,false);
		$this->$attrName = $value;
		
		$this->_class = $config->getProperty("class",false);

		if (!isset($data->submenus)) return;
		
		foreach($data->submenus as $file => $sectionData) {
		    $subsection = new Klear_Model_SubSection;
			$subsection
			    ->setParentMenu($this->_menu)
				->setMainFile($file)
				->setData($sectionData);
			
			$this->_subsections[] = $subsection;
		}
			
	}
	
	
	protected function _getProperty($attribute) {
	    $attributeName = '_' . $attribute . '_i18n';
	    
	    if (isset($this->{$attributeName}[$this->_menu->getCurrentLang()])) {
	        
            return $this->{$attributeName}[$this->_menu->getCurrentLang()];
	    }
	    $attributeName = '_' . $attribute;
		return $this->{$attributeName};
	}
	
	
    public function getName() {
	    return $this->_getProperty('name');    
	}
	
    public function getDescription() {
	    return $this->_getProperty('description');    
	}
	
	
	
	public function __construct() {
		$this->_position = 0;
	}
	
	public function rewind() {
		$this->_position = 0;
	}
	
	public function current() {
		return $this->_subsections[$this->_position];
	}
	
	public function key() {
		return $this->_position;
	}
	
	public function next() {
		++$this->_position;
	}
	
	public function valid() {
		return isset($this->_subsections[$this->_position]);
	
	}	
}
