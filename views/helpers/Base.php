<?php

abstract class Klear_View_Helper_Base extends Zend_View_Helper_Abstract {
	
	protected $_initialized = false;
	protected $_object;	
	
	
	protected function _initHelper($optionName) {
		
		$front = Zend_Controller_Front::getInstance();
		$bootstrap = $front->getParam('bootstrap');
		
		// modules resource ArrayObject contains all bootstrap classes
		// then get the bootstrap for this module (moduleconfig)
		$this->_object = $bootstrap->getResource('modules')->offsetGet('klear')->getOption($optionName);
	}
		
	
}
