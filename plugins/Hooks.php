<?php
/**
 * Plugin encargado de inicializar hooks
 *
 */
class Klear_Plugin_Hooks extends Zend_Controller_Plugin_Abstract
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

        $this->_initHooks();

    }



    protected function _initHooks()
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front
            ->getParam('bootstrap')
            ->getResource('modules')
            ->offsetGet('klear');

        $actionHelpers = $bootstrap->getOption('siteConfig')->getActionHelpers();
        if (sizeof($actionHelpers) > 0) {

            foreach ($actionHelpers as $actionHelper) {

                Zend_Controller_Action_HelperBroker::addHelper(
                    new $actionHelper()
                );
            }
        }
    }

}
