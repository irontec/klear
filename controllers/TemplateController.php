<?php

class Klear_TemplateController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();


        $this->_helper->ContextSwitch()
            ->addActionContext('cache', 'json')
            ->initContext('json');
    }

    public function menuAction()
    {
        $validMenus = array('footerbar','headerbar','info','sidebar');

        if ( ($type = $this->getRequest()->getParam("type")) &&
               (in_array($type, $validMenus)) ) {

            $this->_helper->viewRenderer('menu/' . $type);
        }
    }



    public function cacheAction()
    {
        $cacheTemplates = array(
            "klearSidebarMenu" => "menu/sidebar",
            "klearHeaderbarMenu" => "menu/headerbar",
            "klearFooterbarMenu" => "menu/footerbar",
        );

        /**
         * Cache them all!!
         */

        $templates = array();

        $this->view->setBasePath($this->getFrontController()->getModuleDirectory() . '/views');

        foreach ($cacheTemplates as $template => $action) {
            $templates[$template] = $this->view->render('template/' .  $action . '.phtml');

        }
        $this->view->templates = $templates;

    }

    public function loginAction()
    {
        $this->_helper->viewRenderer('login/form');
    }
}