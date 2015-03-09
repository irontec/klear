<?php
/**
 * Plugin encargado de enviar cookie para request con __downloadToken
 *
 */
class Klear_Plugin_MagicCookie extends Zend_Controller_Plugin_Abstract
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

        $this->_initMagicCookie($request);
    }


    /**
     * Método para comprobar que la descarga
     * @param Zend_Controller_Request_Abstract $request
     */
    protected function _initMagicCookie(Zend_Controller_Request_Abstract $request)
    {
        if ($request->getParam("__downloadToken","") != '') {
        
            $filter = new Zend_Filter_Alnum();
            $token  = $filter->filter($request->getParam("__downloadToken"));
            $expires = gmdate('D, d M Y H:i:s', (time() + 5)) . ' GMT';
            $cookie = new Zend_Http_Header_SetCookie('downloadToken', $token , $expires, '/', null, false, false);
        
            $this->getResponse()->setRawHeader($cookie);
        }
    }
}
