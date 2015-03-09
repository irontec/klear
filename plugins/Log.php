<?php
/**
 * Plugin encargado de instanciar lo mínimo para arrancar el parseo del fichero klear.yaml
 *  Zend_Auth y Zend_Log
 *  
 *  klear.yaml se reparseará con estos recursos disponibles
 *  
 * @author Jabi Infante
 *
 */
class Klear_Plugin_Log extends Zend_Controller_Plugin_Abstract
{
    protected $_mainConfig; 
    
    /**
     * Este método se ejecuta una vez se ha matcheado la ruta adecuada
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::routeShutdown()
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        if (!preg_match("/^klear/", $request->getModuleName())) {
            return;
        }

        $this->_initPlugin();
        $this->_initLog();
    }

    
    protected function _initLog()
    {
        
    
        if (isset($this->_mainConfig->log)) {
            $params = array($this->_mainConfig->log->toArray());
        } else {
            $params = array(
                    array(
                            'writerName' => 'Null'
                    )
            );
        }
    
        Zend_Controller_Action_HelperBroker::addHelper(
                new Klear_Controller_Helper_Log(Zend_Log::factory($params))
        );
    }
    
    protected function _initPlugin()
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam('bootstrap')->getResource('modules')->offsetGet('klear');
        $config = $bootstrap->getOption("configFast");
        if (!isset($config->main)) {
            throw new Klear_Exception_MissingConfiguration('Main section is required on Log Plugin');
        }
        $this->_mainConfig = $config->main;
        
    }

}
