<?php

class Klear_MenuController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	$this->_helper->ContextSwitch()
    			->addActionContext('index', 'json')
    			->initContext('json');

    }

    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data = array();
        foreach ($this->view->menu() as $section) {
            $data['sections'][$section->getName()] = array();
            foreach ($section as $subsection) {
                $data['sections'][$section->getName()]['subsections'][] = array(
                        'sectionId' => $subsection->getMainFile(),
                        'sectionClass' => $subsection->getClass(),
                        'sectionName' => $subsection->getName()
                );
            }
        }

        Zend_Json::$useBuiltinEncoderDecoder = true;

        $jsonResponse = new Klear_Model_DispatchResponse;
        $jsonResponse->setModule('klear');
        $jsonResponse->setPlugin('menu');
        $jsonResponse->addTemplate("/template/menu/type/sidebar", "klearMenu");
        $jsonResponse->addJsFile("");
        $jsonResponse->addCssFile("");
        $jsonResponse->setData($data);
        $jsonResponse->attachView($this->view);
    }

}

