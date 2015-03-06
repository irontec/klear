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

        $this->_registerYamlStream();
        
        $this->_initPlugin();

        $this->_initCacheManager();

        $this->_initConfig();
        $this->_initLayout();
        $this->_initErrorHandler();
        $this->_initHooks();
        $this->_initMagicCookie($request);
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

        //Locale default cache in APPLICATION_PATH . '/cache/'
        $frontendOptions = array(
            'lifetime' => 600,
            'automatic_serialization' => true
        );
        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH . '/cache/'
        );
        $cache = Zend_Cache::factory('Core',
            'File',
            $frontendOptions,
            $backendOptions
        );
        Zend_Locale::setCache($cache);

        $frontend = array(
            'name' => 'File',
            'options' => array(
                'master_files' => array(
                //Este archivo es necesario porque
                //el constructor nos obliga a ello
                    __DIR__ . '/fakeFile'
                ),
                'automatic_serialization' => true,
                'lifetime' => null
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
        $config = $this->_getConfig();

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

    protected function _getConfig()
    {
        if (!isset($this->_config)) {

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
        }

        return $this->_config;
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
     * con el wrapper incluído
     */
    protected function _getConfigPath()
    {
        $moduleConfig = $this->_bootstrap->getOption('config');
        if (!isset($moduleConfig['file'])) {
            throw new Klear_Exception_MissingConfiguration('main config file is required');
        }        
        return 'klear.yaml://' . basename($moduleConfig['file']);
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
    
    /**
     * Método para comprobar que la descarg
     * @param Zend_Controller_Request_Abstract $request
     */
    protected function _initMagicCookie(Zend_Controller_Request_Abstract $request)
    {
        if ($request->getParam("__downloadToken","") != '') {
        
            $filter = new Zend_Filter_Alnum();
            $token  = $filter->filter($request->getParam("__downloadToken"));
            $expires = gmdate('D, d M Y H:i:s', (time() + 5)) . ' GMT';
            $cookie = new Zend_Http_Header_SetCookie('downloadToken', $token , $expires, '/', null, false, false);
        
            $this->getResponse()->setRawHeader($cookie);
        }
    }
}
