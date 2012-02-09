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

        $this->_front = Zend_Controller_Front::getInstance();
        $siteConfig = $this->_bootstrap = $this->_front
                        ->getParam('bootstrap')
                        ->getResource('modules')
                        ->offsetGet('klear')
                        ->getOption('siteConfig');
        
        $data = array(
                    "title" => $siteConfig->getAuthConfig()->getProperty("title"),
                    "description" =>$siteConfig->getAuthConfig()->getProperty("description")
                );
        
        if ($error = $this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $data['error'] = $error;
        }
        
        Zend_Json::$useBuiltinEncoderDecoder = true;

        $jsonResponse = new Klear_Model_DispatchResponse;
        $jsonResponse->setModule('klear');
        $jsonResponse->setPlugin(false); // No requiere plugin
        $jsonResponse->addTemplate("/template/login/form", "klearForm");
        $jsonResponse->addJsFile("");
        $jsonResponse->addCssFile("");
        $jsonResponse->setData($data);
        $jsonResponse->attachView($this->view);
    }

}

