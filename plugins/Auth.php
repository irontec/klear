<?php
/**
 * Plugin encargado de instanciar Zend_Auth si está definido en klear.yaml
 * @author Jabi Infante
 *
 */
class Klear_Plugin_Auth extends Zend_Controller_Plugin_Abstract
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
        $this->_initAuth();
    }


    protected function _initAuth()
    {
        
        if (! ($authAdapterName = $this->_bootstrap->getOption('siteConfig')->getAuthAdapterName()) ) {
            return;
        }
        
        $auth = Zend_Auth::getInstance();
        
    }


    protected function _initAnonymous()
    {
        /* Descomentar cuando se queda en bucle de redireccion por problemas con login */
        //    Zend_Auth::getInstance()->clearIdentity();
        $authAdapter = new App_Auth_Adapter();
        $authAdapter->anonymous();
    }
    
}