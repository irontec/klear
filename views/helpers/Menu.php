<?php

class Klear_View_Helper_Menu extends Klear_View_Helper_Base {
	
	/**
	 * @return Klear_Model_Menu 
	 */
	public function Menu() {
	    return $this->_klearBootstrap->getOption('menu');
	}
}