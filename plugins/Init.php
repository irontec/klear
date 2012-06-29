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
     * @var Zend_Config
     */
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
        $this->_initLog();
        $this->_initLayout();
        $this->_initErrorHandler();
        $this->_registerYamlStream();
        $this->_initHooks();
    }

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
     * Inicializa la configuración principal de Klear
     * y la almacena en el recurso de módulos del bootstrap
     */
    protected function _initConfig()
    {
        /*
         * Cargamos la configuración
        */
        $this->_config = new Zend_Config_Yaml(
                $this->_getConfigPath(),
                APPLICATION_ENV
        );

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

    protected function _initLog()
    {
        if (isset($this->_config->main->log)) {
            $params = array($this->_config->main->log->toArray());
        } else {
            $params = array(
                array(
                    'writerName' => 'Null'
                )
            );
        }
        $log = Zend_Log::factory($params);

        Zend_Controller_Action_HelperBroker::addPath(realpath(__DIR__ . '/../controllers/helpers/'), 'Klear_Action_Helper');
        include_once(__DIR__ . '/../controllers/helpers/Logger.php');
        Zend_Controller_Action_HelperBroker::addHelper(
             new Klear_Action_Helper_Logger($log)
        );
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

    protected function _registerYamlStream()
    {
        stream_wrapper_register("klear.yaml", "Klear_Model_YamlStream");
    }

    protected function _initHooks()
    {
        $actionHelpers = $this->_bootstrap->getOption('siteConfig')->getActionHelpers();
        if (sizeof($actionHelpers) > 0) {
            foreach ($actionHelpers as $actionHelper) {

                Zend_Controller_Action_HelperBroker::addHelper(
                    new $actionHelper()
                );
            }
        }
    }
}
