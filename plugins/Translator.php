<?php
/**
 *
 *
 * @author Lander Ontoria Gardeazabal <lander+dev@irontec.com>
 *
 */
class Klear_Plugin_Translator extends Zend_Controller_Plugin_Abstract
{
    const DEFAULT_REGISTRY_KEY = 'Klear_Translate';

    /**
     * @var Zend_Controller_Front
     */
    protected $_frontController;

    /**
     *
     * @var Zend_Translate
     */
    protected $_translate;

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (!preg_match("/^klear/", $request->getModuleName())) {
            return;
        }

        $this->_initKlearTranslator();
    }

    protected function _initKlearTranslator()
    {
        $this->_frontController = Zend_Controller_Front::getInstance();

        $locale = $this->_getLocale();
        $moduleDirectories = $this->_getModuleDirectories();
        
        foreach ($moduleDirectories as $moduleDirectory) {

            $translationPath = $this->_getTranslationPath($moduleDirectory, $locale);

            if (!file_exists($translationPath)) {
                continue;
            }

            if (!Zend_Registry::isRegistered(self::DEFAULT_REGISTRY_KEY)) {

                $this->_translate = new Zend_Translate(
                    array(
                        'disableNotices' => true,
                        'adapter' => 'Iron_Translate_Adapter_GettextKlear',
                        'content' => $translationPath
                    )
                );

                Zend_Registry::set(self::DEFAULT_REGISTRY_KEY, $this->_translate);

                $this->_setViewHelperTranslator();
                $this->_setActionHelperTranslator();

            } else {

                $this->_translate->getAdapter()->addTranslation($translationPath);

            }
        }
    }

    /**
     * Returns all used module's path. (Basically Klear module path and current module path)
     * @return array
     */
    protected function _getModuleDirectories()
    {
        $moduleDirectories = array();

        $klearPath = $this->_frontController->getModuleDirectory();
        $moduleDirectories[] = $klearPath;

        $currentModule = $this->getRequest()->getParam('moduleName');
        $requestModulePath = $this->_frontController->getModuleDirectory($currentModule);
        if ($requestModulePath != $klearPath) {
            $moduleDirectories[] = $requestModulePath;
        }

        $moduleDirectories[] = APPLICATION_PATH;
        
        return $moduleDirectories;
    }

    /**
     * @return Zend_Locale
     */
    protected function _getLocale()
    {
        $klearBootstrap = $this->_frontController->getParam("bootstrap")->getResource('modules')->offsetGet('klear');
        $siteLanguage = $klearBootstrap->getOption('siteConfig')->getLang();
        return new Zend_Locale($siteLanguage->getLocale());
    }

    /**
     * Returns translation file path
     * @param unknown_type $moduleDirectory
     * @param unknown_type $locale
     * @return string
     */
    protected function _getTranslationPath($moduleDirectory, $locale)
    {

        $translationPath = array(
                $moduleDirectory,
                'languages',
                $locale->toString(),
                $locale->toString() . '.mo'
        );
        
        
        return implode(DIRECTORY_SEPARATOR, $translationPath);
    }

    /**
     * Sets Klear Translator into instanced view
     */
    protected function _setViewHelperTranslator()
    {
        $view = $this->_frontController->getParam("bootstrap")->getResource('view');

        if ($view) {
            $this->_translateHelper = $view->getHelper('Translate');
            $this->_translateHelper->setTranslator($this->_translate);
        } else {
            $logHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('log');
            $logHelper->warn('WARNING: No view resource detected. (resources.view[]="")');
        }
    }

    /**
     * Sets Klear Translator into instanced view
     */
    protected function _setActionHelperTranslator()
    {
        Zend_Controller_Action_HelperBroker::addHelper(
            new Klear_Controller_Helper_Translate($this->_translate)
        );
    }
}
