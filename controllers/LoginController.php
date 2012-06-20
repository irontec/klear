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

        $authConfig = $siteConfig->getAuthConfig();

        $data = array(
                    "title" => $authConfig->getProperty("title"),
                    "description" => $authConfig->getProperty("description")
                );

        $extraInfoLoaderClass = $authConfig->getProperty('loader');
        if ($extraInfoLoaderClass) {
            $extraInfo = new $extraInfoLoaderClass;
            $extraInfo->init();
            $data['extra'] = $extraInfo->getData();
        }


        if ($error = $this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $data['error'] = $error;
        }


        Zend_Json::$useBuiltinEncoderDecoder = true;

        $jsonResponse = new Klear_Model_DispatchResponse;
        $jsonResponse->setModule('klear');
        $jsonResponse->setPlugin(false); // No requiere plugin

        $template = $authConfig->getProperty('template');

        if ($template) {
            $jsonResponse->addTemplate($template, "klearForm");
        } else {
            $jsonResponse->addTemplate("/template/login/form", "klearForm");
        }

        $jsonResponse->setData($data);
        $jsonResponse->attachView($this->view);
    }

}

