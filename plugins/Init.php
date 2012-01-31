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

    public function __construct()
    {
        $this->_front = Zend_Controller_Front::getInstance();
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

        $this->_initErrorHandler();
        $this->_initConfig();
        $this->_initLayout();
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
            APPLICATION_PATH . '/configs/klear/klear.yaml',
            APPLICATION_ENV
        );

        $klearConfig = new Klear_Model_MainConfig();
        $klearConfig->setConfig($config);

		/*
		 * Carga configuración principal de klear
		 */
		$config = new Zend_Config_Yaml(
				APPLICATION_PATH . '/configs/klear/klear.yaml',
				APPLICATION_ENV
		);


		$klearConfig = new Klear_Model_MainConfig();
		$klearConfig->setConfig($config);


		/*
		 * Recupearmos bootstrap para usar su contenedor para guardar
		 */


		$bootstrap = $this->_front->getParam("bootstrap");

		$bootstrap
		        ->getResource('modules')
		        ->offsetGet('klear')
		        ->setOptions(array(
							"siteConfig"=>$klearConfig->getSiteConfig(),
		                    "menu"=>$klearConfig->getMenu(),
		                    "headerMenu"=>$klearConfig->getHeaderMenu(),
		                    "footerMenu"=>$klearConfig->getFooterMenu()
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
        $error
        ->setErrorHandlerModule('klear')
        ->setErrorHandlerController('error')
        ->setErrorHandlerAction('error');
    }
}

