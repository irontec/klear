<?php

/**
 * Clase respuesta Simple para peticiones desde klear.request.js
 * Respuestas simples de acciones concretas
 * No requieren carga de ficheros extra
 *
 * @author jabi
 *
 */
class Klear_Model_SimpleResponse
{
    const RESPONSE_TYPE = 'simple';
    protected $_data;

    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    public function attachView(Zend_View $view)
    {
        $view->data = $this->_data;
        $view->responseType = self::RESPONSE_TYPE;

        $auth = Zend_Auth::getInstance();

        if (!$auth->hasIdentity()) {

            $view->mustLogIn = true;
        }

        return $this;
    }
}