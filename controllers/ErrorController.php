<?php

class Klear_ErrorController extends Zend_Controller_Action
{

	public function init() {
		$this->_helper->ContextSwitch()
				->addActionContext('error', 'json')
				->initContext('json');
	}

	public function errorAction()
    {
        $errors = $this->_getParam('error_handler');


	            $response = array('success' => false);

	            if ($this->getInvokeArg('displayExceptions') == true) {
	                // Add exception error message
	                $response['exception'] = $errors->exception->getMessage();

	                // Send stack trace
	                $response['trace'] = $errors->exception->getTrace();

	                // Send request params
	                $response['request'] = $this->getRequest()->getParams();
	            }

	            $this->view->response = $response;


        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
    }
}
