<?php
/**
 * Plugin encargado de inicializar layout
 * @author Jabi Infante
 *
 */
class Klear_Plugin_Layout extends Zend_Controller_Plugin_Abstract
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

        $this->_initLayout();
    }

    /**
     * Inicializa el Layout de Klear dirigiéndolo a la ruta adecuada
     */
    protected function _initLayout()
    {
        
        $front = Zend_Controller_Front::getInstance();
        /*
         * Indicamos ruta del layout de klear
        */
        Zend_Layout::startMvc();
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayoutPath($front->getModuleDirectory() . '/layouts/scripts');
        $layout->setLayout('layout');
    }

}
