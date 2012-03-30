<?php
/**
 * Plugin encargado de inicializar los recursos necesarios en Klear
 * @author Jabi Infante
 *
 */
class Klear_Plugin_Init extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_Controller_Front
     */
    protected $_front;

    /**
     * @var Klear_Bootstrap
     */
    protected $_bootstrap;

    /**
     * Inicia los atributos utilizados en el plugin
     */
    public function _initPlugin()
    {
        $this->_front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $this->_front
                                 ->getParam('bootstrap')
                                 ->getResource('modules')
                                 ->offsetGet('klear');
    }

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
        $this->_initLayout();        
        $this->_initErrorHandler();
        $this->_registerYamlStream();
    }

    /**
     * Inicializa el Layout de Klear dirigiéndolo a la ruta adecuada
     */
    protected function _initLayout()
    {
        /*
         * Indicamos ruta del layout de klear
        */
        Zend_Layout::startMvc();
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayoutPath($this->_front->getModuleDirectory() . '/layouts/scripts');
        $layout->setLayout('layout');
    }

    /**
     * Inicializa la configuración principal de Klear
     * y la almacena en el recurso de módulos del bootstrap
     */
    protected function _initConfig()
    {
        /*
         * Cargamos la configuración
         */
        $config = new Zend_Config_Yaml(
            $this->_getConfigPath(),
            APPLICATION_ENV
        );

        $klearConfig = new Klear_Model_MainConfig();
        $klearConfig->setConfig($config);

		$this->_bootstrap->setOptions(
            array(
				"siteConfig" => $klearConfig->getSiteConfig(),
                "menu" => $klearConfig->getMenu(),
                "headerMenu" => $klearConfig->getHeaderMenu(),
                "footerMenu" => $klearConfig->getFooterMenu()
            )
		);
		

    }

    protected function _initErrorHandler()
    {
        if ($this->_front->hasPlugin('Zend_Controller_Plugin_ErrorHandler')) {
            $error = $this->_front->getPlugin('Zend_Controller_Plugin_ErrorHandler');
        } else {
            $error = new Zend_Controller_Plugin_ErrorHandler();
            $this->_front->registerPlugin($error);
        }

        $error->setErrorHandlerModule('klear')
              ->setErrorHandlerController('error')
              ->setErrorHandlerAction('error');
    }

    /**
     * Devuelve la ruta al fichero de configuración
     */
    protected function _getConfigPath()
    {
        $configPath = APPLICATION_PATH . '/configs/klear/klear.yaml';
        $moduleConfig = $this->_bootstrap->getOption('config');
        if (isset($moduleConfig['file'])
                && file_exists($moduleConfig['file'])) {
            $configPath = $moduleConfig['file'];
        }
        return $configPath;
    }

    protected function _registerYamlStream() {
        stream_wrapper_register("klear.yaml", "Klear_Model_YamlStream");
    }
    
}
