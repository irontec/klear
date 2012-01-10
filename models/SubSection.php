<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_SubSection extends Klear_Model_Section {
	
	protected $_mainFile;
	protected $_class;
	
	public function setMainFile($file) {
		//TO-DO Excepción cuando no exista el fichero
		$this->_mainFile = $file;
		return $this;
	}
	
    public function getMainFile() {
	    return $this->_mainFile;
	}
	
    public function getClass() {
	    return $this->_class;
	}
	
}
