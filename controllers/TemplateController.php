<?php

class Klear_TemplateController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	$this->_helper->layout->disableLayout();

    }

    public function menuAction()
    {
    	if ($type = $this->getRequest()->getParam("type")) {
	        $this->_helper->viewRenderer('menu/' . $type);
		}
    }

    
    public function loginAction()
    {
        
        $this->_helper->viewRenderer('login/form');
        
    }
}