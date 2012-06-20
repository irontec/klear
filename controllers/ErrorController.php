<?php

class Klear_ErrorController extends Zend_Controller_Action
{

    public function init()
    {

        if ($this->_request->isXmlHttpRequest()) {
                $this
                    ->_helper->ContextSwitch()
                    ->addActionContext('error', 'json')
                    ->initContext('json');
        }

        $this->_helper->ContextSwitch()
            ->addActionContext('list', 'json')
            ->initContext('json');

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
                "yamldecoder"=>"yaml_parse"

            )
        );

        foreach ($config as $errorSection => $aErrors) {
            if (!$aErrors) continue;

            $parsedErrors = new Klear_Model_KConfigParser;
            $parsedErrors->setConfig($aErrors);

            foreach ($aErrors as $code => $msg) {
                $data[$code] = $parsedErrors->getProperty($code, false);
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

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = $this->view->translate('Page not found');
                $this->view->code = 404;
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = $this->view->translate('Application error');
                $this->view->code = 500;
                break;
        }

        if (strtolower(get_class($errors->exception)) == "soapfault") {

            if (!$code = $errors->exception->faultcode) {

                $codeSpec = explode(":", $errors->exception->faultcode);

                if (sizeof($codeSpec) > 1) {
                    $code = $codeSpec[1];
                }
            }

            $this->view->code = $code;

        } else {

            $this->view->code  = $errors->exception->getCode();
        }

        $this->view->message = $errors->exception->getMessage();

    }
}
