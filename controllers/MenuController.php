<?php
class Klear_MenuController extends Zend_Controller_Action
{
    protected $_klearBootstrap;
    protected $_auth;

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->ContextSwitch()
                ->addActionContext('index', 'json')
                ->initContext('json');
        $this->_auth = Zend_Auth::getInstance();
    }

    protected function _getMenu($menuName)
    {
        $menu = [
            'sections' => []
        ];

        foreach ($this->_klearBootstrap->getOption($menuName) as $section) {
            $tmpSection = [
                'id' => $section->getName(),
                'name' => $section->getName(),
                'meta' => $section->getMeta(),
                'description' => $section->getDescription(),
                'opts' => [],
                'subsections' => []
            ];
            $tmpSection = $this->_thinData($tmpSection);
            $subsections = $this->parseSubsections(
                $section->getSubsections()
            );
            $tmpSection['subsections'] = $subsections;

            $menu['sections'][] = $tmpSection;
        }

        return $menu;
    }

    protected function parseSubsections(array $subsections)
    {
        $response = [];
        foreach ($subsections as $subsection) {

            if ($subsection->hasSubsections()) {
                $response[] = [
                    'id' => $subsection->getName(),
                    'name' => $subsection->getName(),
                    'meta' => $subsection->getMeta(),
                    'class' => $subsection->getClass(),
                    'description' => $subsection->getDescription(),
                    'opts' => [],
                    'subsections' => $this->parseSubsections($subsection->getSubsections())
                ];

                continue;
            }

            $tmpSubSection = [
                'id' => $subsection->getMainFile(),
                'name' => $subsection->getName(),
                'meta' => $subsection->getMeta(),
                'class' => $subsection->getClass(),
                'default' =>  $subsection->isDefault(),
                'description' => $subsection->getDescription(),
                'Zpts' => []
            ];

            $tmpSubSection = $this->_thinData($tmpSubSection);
            $response[] = $tmpSubSection;
        }

        return $response;
    }

    protected function _thinData($data) {
        return array_filter($data);
    }

    protected function _getToolsbar()
    {
        return $this->view->Toolsbar();
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

    protected function _getDisableFixed()
    {
        $siteConfig = $this->_klearBootstrap->getOption('siteConfig');

        return $siteConfig->getDisableFixed();
    }

    public function indexAction()
    {
        if (!$this->_auth->hasIdentity()) {
            $this->_forward("hello", "index", "klear");
            return;
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $this->_klearBootstrap = $bootstrap->getResource('modules')->offsetGet('klear');

        $availableMenuSites = array(
            'sidebar'=> $this->_getSidebarMenu(),
            'headerbar'=> $this->_getHeaderMenu(),
            'toolsbar'=> $this->_getToolsbar(),
            'footerbar'=> $this->_getFooterMenu()
        );

        $currentKlearLanguage = Zend_Registry::get('currentSystemLanguage');

        $data = array();
        $data['jqLocale'] = $currentKlearLanguage->getjQLocale();
        $data['navMenus'] = $availableMenuSites;
        $data['disableFixed'] = $this->_getDisableFixed();

        $jsonResponse = Klear_Model_DispatchResponseFactory::build();

        $jsonResponse->addTemplate("/template/menu/type/sidebar", "klearSidebarMenu");
        $jsonResponse->addTemplate("/template/menu/type/headerbar", "klearHeaderbarMenu");
        $jsonResponse->addTemplate("/template/menu/type/footerbar", "klearFooterbarMenu");
        $jsonResponse->addTemplate("/template/menu/type/info", "klearInfoBar");

        $jsonResponse->setData($data);
        $jsonResponse->attachView($this->view);
    }
}