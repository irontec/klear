<?php
/**
 * Plugin encargado de inicializar los recursos necesarios en Klear
 * @author Jabi Infante
 *
 */
class Klear_Plugin_Config extends Zend_Controller_Plugin_Abstract
{
    protected $_bootstrap;
    protected $_config;
    

    /**
     * Este método que se ejecuta una vez se ha matcheado la ruta adecuada
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::routeShutdown()
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        if (!preg_match("/^klear/", $request->getModuleName())) {
            return;
        }
        $this->_initPlugin();
        $this->_initConfig();
        
        $this->_releaseConfig();

    }

    /**
     * Inicializa la configuración principal de Klear
     * y la almacena en el recurso de módulos del bootstrap
     */
    protected function _initConfig()
    {

        $klearConfig = new Klear_Model_MainConfig();
        $klearConfig->setConfig($this->_config);

        $this->_bootstrap->setOptions(
            array(
                "siteConfig" => $klearConfig->getSiteConfig(),
                "menu" => $klearConfig->getMenu(),
                "headerMenu" => $klearConfig->getHeaderMenu(),
                "footerMenu" => $klearConfig->getFooterMenu()
            )
        );
    }
    
    protected function _initPlugin()
    {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap')->getResource('modules')->offsetGet('klear');
        $config = $this->_bootstrap->getOption("config");
        if (!isset($config->main)) {
            throw new Klear_Exception_MissingConfiguration('Main section is required on ConfigPlugin');
        }
        $this->_config = $config;
    }
    
    protected function _releaseConfig()
    {
        $this->_bootstrap->setOptions(array(
                "config"=>null,
                "configFast"=>null
        ));
    }

}
