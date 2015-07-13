<?php
/**
 * Inicializa Error Handler
 *
 */
class Klear_Plugin_Error extends Zend_Controller_Plugin_Abstract
{

    protected $_defaultErrorMessage;
    protected $_lastException;
    protected $_front;
    protected $_bootstrap;
    protected $_logger;

    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->_front = \Zend_Controller_Front::getInstance();
        $this->_front->throwExceptions(true);
        $this->_bootstrap = $this->_front->getParam('bootstrap');
        $this->_logger = $this->_bootstrap->getResource('log');
        if (is_null($this->_logger)) {
            $params = array(
                    array(
                            'writerName' => 'Null'
                    )
            );
            $this->_logger = Zend_Log::factory($params);
        }
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

        $this->_initErrorHandler();
    }



    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $options = $this->_bootstrap->getOptions();
        if (isset($options["resources"]["throwexceptions"])) {
            $this->_front->throwExceptions($options["resources"]["throwexceptions"]);
        } else {
            $this->_front->throwExceptions(false);
        }

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

        $this->_defaultErrorMessage = "<h2>Internal Server Error</h2>".
                "<p>Please contact the server administrator.</p>".
                "<p>More information about this error may be availabe in the server syslog.</p>";

        set_exception_handler(array($this, "fallback_exception_handler"));
        register_shutdown_function(array($this, "fatal_handler"));
    }

    public function fatal_handler ()
    {
        if (!is_null($this->_lastException)) {
            return;
        }

        $error = error_get_last();

        $isUnhandledException = empty($error);
        if ($isUnhandledException) {
            return;
        }

        $critError = false;
        $critictErrors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR);
        if (in_array($error["type"], $critictErrors)) {
            $critError = true;
        }

        $errorMsg = $error['message'] . " (".$error['type'].") in " .
                    $error["file"] . " +" . $error["line"];


        $logLevel = $critError == true ? 1 : 5;
        $this->_logger->log($errorMsg, $logLevel);

        if (!$critError) {
            return;
        }
        if (ini_get("display_errors")) {
            throw new \Exception($errorMsg, 1);
        }

        http_response_code(500);
        echo $this->_defaultErrorMessage;
    }

    public function fallback_exception_handler (\Exception $exception)
    {

        $currentException = $exception;
        if (!is_null($exception->getPrevious())) {
            $currentException = $exception->getPrevious();
        }
        $this->_lastException = $currentException;

        $errorMsg = $currentException->getMessage() . " (".$currentException->getCode().") # ".
            $currentException->getFile()."(".$currentException->getLine().")";
        $this->_logger->log($errorMsg, 2);

        if (ini_get("display_errors")) {
            throw $currentException;
        }
        http_response_code(500);
        echo $this->_defaultErrorMessage;
    }
}
