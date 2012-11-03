<?php

class Klear_MenuController extends Zend_Controller_Action
{
    protected $_klearBootstrap;

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->ContextSwitch()
                ->addActionContext('index', 'json')
                ->initContext('json');
    }

    protected function _getMenu($menuName)
    {
        $menu = array();
        foreach ($this->_klearBootstrap->getOption($menuName) as $section) {
            $tmpSection = array(
                    'sectionId' => $section->getName(),
                    'sectionClass' => $section->getName(),
                    'sectionName' => $section->getName(),
                    'sectionDescription' => $section->getDescription(),
                    'sectionOpts' => array()
            );
            foreach ($section as $subsection) {
                $tmpSubSection = array(
                        'sectionId' => $subsection->getMainFile(),
                        'sectionClass' => $subsection->getClass(),
                        'sectionName' => $subsection->getName(),
                        'default' =>  $subsection->isDefault(),
                        'sectionDescription' => $subsection->getDescription(),
                        'sectionOpts' => array()
                );
                $tmpSection['subsections'][] = $tmpSubSection;
            }
            $menu['sections'][] = $tmpSection;
        }
        return $menu;
    }



    protected function _getHeaderMenu()
    {
        return $this->_getMenu('headerMenu');
    }

    protected function _getSidebarMenu()
    {
        return $this->_getMenu('menu');
    }

    protected function _getFooterMenu()
    {
        return $this->_getMenu('footerMenu');
    }

    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $this->_klearBootstrap = $bootstrap->getResource('modules')->offsetGet('klear');

        $availableMenuSites = array(
            'sidebar'=> $this->_getSidebarMenu(),
            'headerbar'=> $this->_getHeaderMenu(),
            'footerbar'=> $this->_getFooterMenu()
        );


        $currentKlearLanguage = Zend_Registry::get('currentSystemLanguage');

        $data = array();
        $data['jqLocale'] = $currentKlearLanguage->getjQLocale();
        $data['navMenus'] = $availableMenuSites;

        $jsonResponse = Klear_Model_DispatchResponseFactory::build();

        $jsonResponse->addTemplate("/template/menu/type/sidebar", "klearSidebarMenu");
        $jsonResponse->addTemplate("/template/menu/type/headerbar", "klearHeaderbarMenu");
        $jsonResponse->addTemplate("/template/menu/type/footerbar", "klearFooterbarMenu");
        $jsonResponse->addTemplate("/template/menu/type/info", "klearInfoBar");

        $jsonResponse->setData($data);
        $jsonResponse->attachView($this->view);
    }

}

