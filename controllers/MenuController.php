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

    protected function _getHeaderMenu()
    {
        $klearHeaderMenu = array();
        foreach ($this->_klearBootstrap->getOption('headerMenu') as $section) {
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
                        'sectionDescription' => $subsection->getDescription(),
                        'sectionOpts' => array()
                );
                $tmpSection['subsections'][] = $tmpSubSection;
            }
            $klearHeaderMenu['sections'][] = $tmpSection;
        }
        return $klearHeaderMenu;
    }

    protected function _getSidebarMenu()
    {
        $klearSidebarMenu = array();
        foreach ($this->_klearBootstrap->getOption('menu') as $section) {
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
                        'sectionDescription' => $subsection->getDescription(),
                        'sectionOpts' => array()
                );
                $tmpSection['subsections'][] = $tmpSubSection;
            }
            $klearSidebarMenu['sections'][] = $tmpSection;
        }
        return $klearSidebarMenu;
    }

    protected function _getFooterMenu()
    {
        $klearFooterMenu = array();
        foreach ($this->_klearBootstrap->getOption('footerMenu') as $section) {
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
                        'sectionDescription' => $subsection->getDescription(),
                        'sectionOpts' => array()
                );
                $tmpSection['subsections'][] = $tmpSubSection;
            }
            $klearFooterMenu['sections'][] = $tmpSection;
        }
        return $klearFooterMenu;
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

        $data = array();

        $data['navMenus'] = $availableMenuSites;

        Zend_Json::$useBuiltinEncoderDecoder = true;

        $jsonResponse = new Klear_Model_DispatchResponse;
        $jsonResponse->setModule('klear');
        $jsonResponse->setPlugin(false); // no requiere plugin
        $jsonResponse->addTemplate("/template/menu/type/sidebar", "klearSidebarMenu");
        $jsonResponse->addTemplate("/template/menu/type/headerbar", "klearHeaderbarMenu");
        $jsonResponse->addTemplate("/template/menu/type/footerbar", "klearFooterbarMenu");
        $jsonResponse->setData($data);
        $jsonResponse->attachView($this->view);
    }

}

