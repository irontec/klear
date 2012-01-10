<?php

class Klear_View_Helper_Menu extends Klear_View_Helper_Base {
	
	public function Menu() {
		if ($this->_initialized === false) $this->_initHelper('menu');
		return $this->_object;	
	}
}