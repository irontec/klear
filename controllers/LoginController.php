<?php

class Klear_LoginController extends Zend_Controller_Action
{
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
        $siteConfig = $this->_front
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

        $this->_helper->log('new KlearLogin');

        if ($error = $this->_helper->getHelper('FlashMessenger')->getMessages()) {
            $data['error'] = array_shift($error);
        }

        if ($extraInfoLoaderClass) {

            $this->_helper->log('KlearLogin with extraInfoClass:' . $extraInfoLoaderClass);
            $extraInfo = new $extraInfoLoaderClass;

            $extraInfo->init($data);

            $data['extra'] = $extraInfo->getData();

        } else {

            $this->_helper->log('klearLogin with no extraInfoLoaderClass');
        }


        $jsonResponse = Klear_Model_DispatchResponseFactory::build();
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

