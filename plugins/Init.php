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
        $this->_initCacheManager();
        $this->_initConfig();
        $this->_initAuthStorage();
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

    protected function _initCacheManager()
    {
        $bootstrap = Zend_Controller_Front::getInstance()
                        ->getParam('bootstrap');
        $cacheManager = $bootstrap->getResource('cachemanager');

        if (!$cacheManager) {
            $cacheManager = new Zend_Cache_Manager();
            $bootstrap->getContainer()->cachemanager = $cacheManager;
        }

        $frontend = array(
            'name' => 'File',
            'options' => array(
                'master_files' => array(
                    '/dev/null'
                ),
                'automatic_serialization' => true
            )
        );

        if (!$cacheManager->hasCacheTemplate('klearconfig')) {

            $cache = array(
                'frontend' => $frontend,
                'backend' => array(
                    'name' => 'File',
                    'options' => array(
                        'cache_dir' => APPLICATION_PATH . '/cache'
                    )
                )
            );

            $cacheManager->setCacheTemplate('klearconfig', $cache);

        } else {

            $cacheManager->setTemplateOptions(
                'klearconfig',
                array('frontend' => $frontend)
            );
        }
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
        $configFile = $this->_getConfigPath();

        $cache = $this->_getCache($configFile);
        $this->_config = $cache->load(md5($configFile));

        if (!$this->_config) {

            $this->_config = new Zend_Config_Yaml(
                $configFile,
                APPLICATION_ENV,
                array(
                    "yamldecoder" => "yaml_parse"
                )
            );

            $cache->save($this->_config);
        }

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

    protected function _getCache($filePath)
    {
        $cacheManager = Zend_Controller_Front::getInstance()
        ->getParam('bootstrap')
        ->getResource('cachemanager');

        $cache = $cacheManager->getCache('klearconfig');
        $cache->setMasterFile($filePath);
        return $cache;
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

    protected function _initAuthStorage()
    {
        $auth = Zend_Auth::getInstance();

        $sessionName = 'klear_auth';

        if (isset($this->_config->main->auth->session)) {
            $authSession = $this->_config->main->auth->session;

            // We don't want to change the session_name in this case
            if (isset($authSession->disableChangeName) && $authSession->disableChangeName) {
                return;
            }

            if (isset($authSession->name)) {
                $sessionName = $authSession->name;
            }
        }

        $auth->setStorage(new Zend_Auth_Storage_Session($sessionName));
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
        Zend_Controller_Action_HelperBroker::addHelper(
            new Klear_Controller_Helper_Log($log)
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
