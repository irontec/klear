<?php

class Klear_LoginController extends Zend_Controller_Action
{
    protected $_klearBootstrap;

    public function init()
    {
        /* Initialize action controller here */
    	$this->_helper->ContextSwitch()
    			->addActionContext('index', 'json')
    			->initContext('json');

    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	
    	
    }

    
    public function indexAction()
    {

       
        Zend_Json::$useBuiltinEncoderDecoder = true;

        $jsonResponse = new Klear_Model_DispatchResponse;
        $jsonResponse->setModule('klear');
        $jsonResponse->setPlugin(false); // No requiere plugin
        $jsonResponse->addTemplate("/template/login/form", "klearForm");
        $jsonResponse->addJsFile("");
        $jsonResponse->addCssFile("");
        $jsonResponse->setData(array());
        $jsonResponse->attachView($this->view);
    }

}

