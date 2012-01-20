<?php

abstract class Klear_View_Helper_Base extends Zend_View_Helper_Abstract {
	
	/**
	 * @var Klear_Bootstrap
	 */
	protected $_klearBootstrap;	
	
	public function __construct(){
	    $front = Zend_Controller_Front::getInstance();
	    $bootstrap = $front->getParam('bootstrap');
	    $this->_klearBootstrap = $bootstrap->getResource('modules')->offsetGet('klear');
	}
}
