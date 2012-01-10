<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_Section  implements Iterator {
	
	protected $_name;
	protected $_nameML = array();
	
	protected $_description;
	protected $_descriptionML = array();
	
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
	
	
	protected function _setProperty($attribute,$data) {
		if (!isset($data)) return;
		
		$attributeValue = '_'.$attribute;
		
		if (is_string($data)) {
			$this->{$attributeValue} = $data;
			return;
		}
		
		/*
		 * El atributo tiene multi-idioma
		 */
		$attributeValue .= "ML";
		
		if ( (is_object($data)) && (isset($data->ml)) ) {
		    
			foreach ($data->ml as $lang => $_data) {
			    $this->{$attributeValue}[$lang] = $_data;
			}
		}
	}
	
	public function setData(Zend_Config $data) {
		
		
		// Si tenemos la sección title, se sobreescribe el name.
		// Soporte Multi-idioma integrado
		$this->_setProperty("name",$data->title);
		$this->_setProperty("description",$data->desc);
		$this->_setProperty("class",$data->class);
        
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
	    $attributeName = '_' . $attribute . 'ML';
	    
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
