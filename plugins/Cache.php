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
        try {
            $this->_initCacheManager();
            $this->_initCacheForLocale();
        } catch(Exception $e) {

            $request->setControllerName('error');
            $request->setActionName('error');
            
            // Set up the error handler
            $error = new Zend_Controller_Plugin_ErrorHandler();
            $error->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
            $error->request = clone($request);
            $error->exception = $e;
            $request->setParam('error_handler', $error);
            
        }
        
    }


    protected function _initCacheManager()
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $cacheManager = $bootstrap->getResource('cachemanager');

        if (!$cacheManager) {
            $cacheManager = new Zend_Cache_Manager();
            $bootstrap->getContainer()->cachemanager = $cacheManager;
        }

        $frontend = array(
            'name' => 'File',
            'options' => array(
                'master_files' => array(__FILE__),
                'automatic_serialization' => true,
                'ignore_missing_master_files'=>true
            )
        );

        if (!$cacheManager->hasCacheTemplate('klearconfig')) {

            $cache = array(
                'frontend' => $frontend,
                'backend' => array(
                    'name' => 'File',
                    'options' => array(
                        'cache_dir' => APPLICATION_PATH . '/cache',
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
   
   protected function _initCacheForLocale()
   {
       $frontendOptions = array(
               'lifetime' => 7200,
               'automatic_serialization' => true
       );
       
       $backendOptions = array(
           'cache_dir' => APPLICATION_PATH . '/cache/'
       );
       
       
       $cache = \Zend_Cache::factory(
                    'Core',
                    'File',
                    $frontendOptions,
                    $backendOptions
                );
       
       Zend_Locale::setCache($cache);

   }

}
