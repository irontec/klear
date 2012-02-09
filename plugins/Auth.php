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
        $this->_initAuth($request);
    }


    protected function _initAuth(Zend_Controller_Request_Abstract $request)
    {
        
        if ( (false === ($authConfig = $this->_bootstrap->getOption('siteConfig')->getAuthConfig()) )   || 
            (!$authConfig->exists("adapter") )
            ) {

            // La instancia de klear no tiene autenticación 
            return true;
        }
        
        $auth = Zend_Auth::getInstance();

        
        if ((bool)$request->getPost("klearLogin")) {
            
            $authAdapterName = $authConfig->getProperty("adapter");
            
            $authAdapter = new $authAdapterName($request);
            $oResult = $auth->authenticate($authAdapter);
            
            if ($oResult->isValid()) {
                
                $authAdapter->saveStorage();
                $session = new Zend_Session_Namespace('Zend_Auth');
                $session->setExpirationSeconds(86400);
                if ($request->getParam('remember', '') == 'true') {
                    Zend_Session::rememberMe();
                }
            
            } else {
                
                $messages = $oResult->getMessages();
                $request->setParam('loginError', $messages['message']);
            }
        }
        
    }

    
}