<?php
/**
 * TODO: Habría que definir X tipos de excepciones del propio Klear
 *       que se capturasen en el ErrorController y enviasen al cliente
 *       los codigos de error y mensajes necesarios.
 *       example: throw new \KlearMatrix_Exception_MissingParameter('modelFile must be specified in ' . $this->_item->getType() . 'configuration', ###);
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
        $config = new Zend_Config_Yaml(
            $filePath,
            APPLICATION_ENV,
            array(
                "yamldecoder" => "yaml_parse"
            )
        );

        $data = array();

        foreach ($config as $aErrors) {

            if (!$aErrors) {
                continue;
            }

            $parsedErrors = new Klear_Model_ConfigParser;
            $parsedErrors->setConfig($aErrors);

            foreach ($aErrors as $code => $msg) {
                $data[$code] = $parsedErrors->getProperty($code);
            }
        }

        $jsonResponse = new Klear_Model_DispatchResponse();
        $jsonResponse->setModule('klear');
        $jsonResponse->setPlugin(false); // no requiere plugin
        $jsonResponse->setData($data);
        $jsonResponse->attachView($this->view);

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
                $this->view->message = $this->view->translate('Page not found');
                $this->view->code = 404;

                if (APPLICATION_ENV == 'development') {
                    if (isset($errors->exception->file)) {
                        $this->view->file = $errors->exception->file;
                    }
                    if (isset($errors->exception->line)) {
                        $this->view->line = $errors->exception->line;
                    }
                }
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
//                 $this->view->message = $this->view->translate('Application error');
                $this->view->message = $errors->exception->getMessage();
                $this->view->code = 500;

                if (APPLICATION_ENV == 'development') {
                    $this->view->file = $errors->exception->getFile();
                    $this->view->line = $errors->exception->getLine();
                }
                break;
        }

        $exceptionType = strtolower(get_class($errors->exception));
        switch ($exceptionType)
        {
            case 'soapfault':
                if (!$code = $errors->exception->faultcode) {

                    $codeSpec = explode(":", $errors->exception->faultcode);

                    if (sizeof($codeSpec) > 1) {
                        $code = $codeSpec[1];
                    }
                }
                $this->view->code = $code;
                $this->view->message = $errors->exception->getMessage();
                break;
        }

        $this->_helper->log('Exception captured ['.$this->view->code.']: ' .$this->view->message, Zend_Log::ERR);
    }

}
