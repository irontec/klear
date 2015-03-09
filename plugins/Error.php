<?php
/**
 * Inicializa Error Handler
 *
 */
class Klear_Plugin_Error extends Zend_Controller_Plugin_Abstract
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

        $this->_initErrorHandler();
    }

    protected function _initErrorHandler()
    {
        
        $front = Zend_Controller_Front::getInstance();
        
        if ($front->hasPlugin('Zend_Controller_Plugin_ErrorHandler')) {
            $error = $front->getPlugin('Zend_Controller_Plugin_ErrorHandler');
        } else {
            $error = new Zend_Controller_Plugin_ErrorHandler();
            $front->registerPlugin($error);
        }

        $error->setErrorHandlerModule('klear')
              ->setErrorHandlerController('error')
              ->setErrorHandlerAction('error');
    }
}
