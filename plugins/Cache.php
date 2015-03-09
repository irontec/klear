<?php
/**
 * Inicializar Cache Manager
 *
 */
class Klear_Plugin_Cache extends Zend_Controller_Plugin_Abstract
{
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

        $this->_initCacheManager();

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

}
