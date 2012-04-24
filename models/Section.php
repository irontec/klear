<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_Section  implements Iterator {

	protected $_name;

	protected $_description;

	protected $_menu = null;

	protected $_subsections;
	protected $_position = 0;

	protected $_skip = array();

	public function setName($name) {
		$this->_name = $name;
		return $this;
	}

	public function setParentMenu($menu) {
	    $this->_menu = $menu;
	    return $this;
	}
	/*
	 * skip subsections
	 */
	public function setDataToSkip($skip)
	{
	    $this->_skip = $skip;
	    return $this;
	}

	public function setData(Zend_Config $data) {

		$config = new Klear_Model_KConfigParser();
		$config->setConfig($data);

		$this->_name = $config->getProperty("title",true);
		$this->_description = $config->getProperty("description",false);

		$this->_class = $config->getProperty("class",false);

		if (!isset($data->submenus)) return;

		foreach($data->submenus as $file => $sectionData) {
		    if (in_array($file, $this->_skip)) continue;
		    $subsection = new Klear_Model_SubSection;

			$subsection
			    ->setParentMenu($this->_menu)
				->setMainFile($file)
				->setData($sectionData);

			$this->_subsections[] = $subsection;
		}

	}

	public function getName() {
	    return $this->_name;
	}

    public function getDescription() {
	    return $this->_description;
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
