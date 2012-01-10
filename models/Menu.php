<?php

class Klear_Model_Menu implements Iterator
{
	
	protected $_siteConfig;
	protected $_sections = array();
	protected $_position = 0;
	
	public function setName($name)
	{
		$this->_name = $name;
	}

	public function setDescription($description) {
		$this->_description = $description;
	}
	
	public function __construct() {
		$this->_position = 0;
	}
	
	public function rewind() {
		$this->_position = 0;
	}
	
	public function current() {
		return $this->_sections[$this->_position];
	}
	
	public function key() {
		return $this->_position;
	}
	
	public function next() {
		++$this->_position;
	}
	
	public function valid() {
		return isset($this->_sections[$this->_position]);
	
	}
	
	public function setConfig(Zend_Config $config)
	{
		$this->_config = $config;
	}
	
	public function getCurrentLang()
	{
	    return $this->_siteConfig->getLang();
	}
	
	public function setSiteConfig(Klear_Model_SiteConfig $siteConfig)
	{
		$this->_siteConfig = $siteConfig;
	}
	
	public function parse() {
		foreach($this->_config as $name => $sectionData) {
			
			$section = new Klear_Model_Section;
			$section
			    ->setParentMenu($this)
				->setName($name)
				->setData($sectionData);
			
			$this->_sections[] = $section;
		}
		
		$this->_config = null;
	}
	
	
	
	
}