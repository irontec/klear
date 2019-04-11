<?php
/**
 * TODO: Habría que definir X tipos de excepciones del propio Klear
 *       que se capturasen en el ErrorController y enviasen al cliente
 *       los codigos de error y mensajes necesarios.
 *       example: throw new \KlearMatrix_Exception_MissingParameter
 *          ('modelFile must be specified in ' . $this->_item->getType() . 'configuration', ###);
 */
class Klear_ErrorController extends Zend_Controller_Action
{

    public function init()
    {
        $contextSwitch = $this->_helper->ContextSwitch();
        if ($this->_request->isXmlHttpRequest()) {
            $contextSwitch->addActionContext('error', 'json');
        }
        $contextSwitch->addActionContext('list', 'json');
        $contextSwitch->initContext('json');
    }

    public function listAction()
    {
        $filePath = 'klear.yaml://errors.yaml';

        /*
         * Carga configuración de la sección cargada según la request.
        */
        $cache = $this->_getCache($filePath);

        $keyGenerator = new \Klear_Model_CacheKeyGenerator($filePath);
        // En principio los errores no contendrán valores ${auth.*}
        $keyGenerator->ignoreSessionAuth();
        $cacheKey = $keyGenerator->getKey();

        $config = $cache->load($cacheKey);

        if (!$config) {
            $config = new Zend_Config_Yaml(
                $filePath,
                APPLICATION_ENV,
                array(
                    "yamldecoder" => "yaml_parse"
                )
            );
            $cache->save($config);
        }

        $data = array();

        foreach ($config as $aErrors) {

            if (!$aErrors) {
                continue;
            }

            $parsedErrors = new Klear_Model_ConfigParser;
            $parsedErrors->setConfig($aErrors);

            foreach ($aErrors as $code => $msg) {

                if (is_string($msg)) {
                    // Si es un string, no será una estructura i18n(deprecated)
                    // Lo pasamos por gettext por si utilizará _("literal")
                    $data[$code] = Klear_Model_Gettext::gettextCheck($msg);
                } else {
                    // es una estructura multi-idioma
                    // ConfigParser se encarga de traducir
                    $data[$code] = $parsedErrors->getProperty($code);
                }

            }
        }

        $jsonResponse = Klear_Model_DispatchResponseFactory::build();
        $jsonResponse->setData($data);
        $jsonResponse->attachView($this->view);
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

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        $this->view->error = true;

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = $this->_helper->translate('Page not found');
                $this->view->code = 404;

                if (APPLICATION_ENV == 'development') {
                    $this->view->file = $errors->exception->getFile();
                    $this->view->line = $errors->exception->getLine();
                    $this->view->traceString = $errors->exception->getTraceAsString();
                }
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = $errors->exception->getMessage();
                $this->view->code = 500;
                $this->view->exceptionCode = $errors->exception->getCode();

                if ($this->_showErrors()) {
                    $this->view->file = $errors->exception->getFile();
                    $this->view->line = $errors->exception->getLine();
                    $this->view->traceString = $errors->exception->getTraceAsString();
                } else {
                    // Si no estamos en desarrollo, y no tenemo ćodigo de excepción, es probable
                    // que estemos ante un error de PHP. (lo ocultamos ;)
                    $isDomainException = $errors->exception instanceof \DomainException;

                    if (!$isDomainException && $this->view->exceptionCode == 0) {
                        $this->view->message = $this->_helper->translate('Undefined error');
                    }
                }

                break;
        }

        if (!$this->_request->isXmlHttpRequest()) {
            Zend_Layout::getMvcInstance()->disableLayout();
        }

        $eCode = $errors->exception->getCode();
        $eFile = $errors->exception->getFile();
        $eLine = $errors->exception->getLine();
        $eMessage = $errors->exception->getMessage();
        $logMessage = 'Exception captured ['.$this->view->code.']: '.
            $eMessage." (".$eCode.") # ".$eFile." (".$eLine.")";
        $this->_helper->log($logMessage, Zend_Log::ERR);
    }

    protected function _showErrors()
    {
        $phpSettings = $this->getInvokeArg('bootstrap')->getOption("phpSettings");
        $showErrors = false;
        if (array_key_exists("display_errors", $phpSettings) && $phpSettings["display_errors"]) {
            $showErrors = true;
        }
        if (APPLICATION_ENV == 'development' || $showErrors) {
            $showErrors = true;
        }
        return $showErrors;
    }

}
