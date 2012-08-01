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
        $validMenus = array('footerbar','headerbar','info','sidebar');

        if ( ($type = $this->getRequest()->getParam("type")) &&
               (in_array($type, $validMenus)) ) {

            $this->_helper->viewRenderer('menu/' . $type);
        }
    }


    public function loginAction()
    {
        $this->_helper->viewRenderer('login/form');
    }
}