<?php
/**
 * Plugin encargado de instanciar lo mínimo para arrancar el parseo del fichero klear.yaml
 *  Zend_Auth y Zend_Log
 *  
 *  klear.yaml se reparseará con estos recursos disponibles
 *  
 * @author Jabi Infante
 * @author ClassName >> Mikel Madariaga
 *
 */
class Klear_Plugin_InitAuthAndLog extends Zend_Controller_Plugin_Abstract
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
        
        $this->_initConfig();
        $this->_initLog();   
        $this->_initAuthStorage();
        $this->_initAuth($request);
        
        $this->_postLogin();
    }

    protected function _initConfig() {
        $configFile = $this->_getConfigPath();
        $fullConfig = new Zend_Config_Yaml(
                $configFile,
                APPLICATION_ENV,
                array(
                        "yamldecoder" => "yaml_parse"
                )
        );
        
        $this->_mainConfig = $fullConfig->main;
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
    
    protected function _initAuth(Zend_Controller_Request_Abstract $request)
    {
        $authConfig = $this->_getAuthConfig();
        $logHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('log');
        if ((false === $authConfig) || !$authConfig->exists("adapter")) {
            // La instancia de klear no tiene autenticación
            $logHelper->log('No auth adapter found.');
            return;
        }
        
        if ((bool)$request->getPost("klearLogin")) {
            $auth = Zend_Auth::getInstance();

            $authAdapterName = $authConfig->getProperty("adapter");
            $logHelper->log('Auth adapter: ' . $authAdapterName);

            $authAdapter = new $authAdapterName($request, $authConfig);
            $authResult = $auth->authenticate($authAdapter);

            if ($authResult->isValid()) {

                $authAdapter->saveStorage();
                $session = new Zend_Session_Namespace('Zend_Auth');

                $logHelper->log(
                    'User ' . $auth->getIdentity()->getLogin()
                    .' (' . get_class($auth->getIdentity()) . ') logged in'
                );

                $session->setExpirationSeconds(86400);
                
                if ($request->getParam('remember', '') == 'true') {
                    Zend_Session::rememberMe();
                }
                
                
            } else {

                $messages = $authResult->getMessages();

                $logHelper->log('Invalid credentials for user ' . $request->getPost('username', ''));
                $logHelper->log('LoginError: ' . print_r($messages, true));

                $request->setParam('loginError', $messages);

            }
        }
    }

    protected function _postLogin()
    {
        
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (is_object($identity) && method_exists($identity, 'postLogin') ) {
            $identity->postLogin();
        }
    }
    
    
    protected function _initAuthStorage()
    {
        $auth = Zend_Auth::getInstance();
    
        $sessionName = 'klear_auth';
    
        $authConfig = $this->_getAuthConfig();
    
        if (isset($authConfig->session)) {
            $authSession = $authConfig->session;
    
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
    
    protected function _getAuthConfig()
    {
        if (!isset($this->_authConfig)) {

            $siteConfig = new Klear_Model_SiteConfig();
            $siteConfig->setConfigForAuth($this->_mainConfig);
            
            $this->_authConfig = $siteConfig->getAuthConfig();
            
        }
        
        return $this->_authConfig;
    }
    
    /**
     * Devuelve la ruta al fichero de configuración
     */
    protected function _getConfigPath()
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front
                        ->getParam('bootstrap')
                        ->getResource('modules')
                        ->offsetGet('klear');
        
        $moduleConfig = $bootstrap->getOption('config');
        if (!isset($moduleConfig['file'])) {
            throw new Klear_Exception_MissingConfiguration('main config file is required');
        }
        return $moduleConfig['file'];
    }
    

}
