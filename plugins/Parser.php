<?php
/**
 * Plugin encargado de inicializar ambas versiones de klear.yaml
 *
 */
class Klear_Plugin_Parser extends Zend_Controller_Plugin_Abstract
{

    protected $_bootstrap;
    protected $_configFilePath;
    protected $_filePath;
    protected $_bootstrapConfigIden = 'config';
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
        $this->_initParser();

    }

    protected function _initParser()
    {
        $cacheKey = $this->_getCacheKey();
        $cache = $this->_getCache();
        
        $config = $cache->load($cacheKey);
        
        if (!$config) {

            $config = new Zend_Config_Yaml(
                    $this->_filePath,
                    APPLICATION_ENV,
                    array(
                            "yamldecoder" => "yaml_parse"
                    )
            );
            $cache->save($config);
        }
        
        $this->_bootstrap->setOptions(array($this->_bootstrapConfigIden => $config));
    }
        
    
    protected function _initPlugin()
    {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap')->getResource('modules')->offsetGet('klear');

        $this->_configFilePath = $this->_bootstrap->getOption("configFilePath");
        if (!$this->_configFilePath) {
            throw new Klear_Exception_MissingConfiguration('Confgi File Path is required on Parser Plugin');
        }
        $this->_filePath = 'klear.yaml://' . basename($this->_configFilePath);
    }
    
    protected function _getCacheKey()
    {
        
        $baseKey = $this->_filePath;
        $identity = Zend_Auth::getInstance()->getIdentity();
        if ($identity) {
            $baseKey .= $identity->getId();
        } else {
            $baseKey .= "UNKOWNN_ID";
        }
        return md5($baseKey);
    }
    
    protected function _getCache()
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $cacheManager = $bootstrap->getResource('cachemanager');
        if (!$cacheManager) {
            throw new Klear_Exception_MissingConfiguration('Cache manager initializated is required on Parser Plugin');
        }
        
        $cache = $cacheManager->getCache('klearconfig');
        $cache->setMasterFile($this->_configFilePath);
        return $cache;
    }
    
    
    

}
