<?php

class Klear_Bootstrap extends Zend_Application_Module_Bootstrap
{
    /**
     * Registramos los plugins necesarios para el correcto funcionamiento de Klear
     */
    protected function _initKlear()
    {

        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Klear_Plugin_Init());

        /*
         * Klear_Plugin_Auth if enabled in klear.yaml
        */
        $front->registerPlugin(new Klear_Plugin_Auth());

        /*
         * Klear_Plugin_Translator lander
         */
        $front->registerPlugin(new Klear_Plugin_Translator());

    }

    protected function _initModuleRoutes()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $router = $frontController->getRouter();

        $router->addRoute(
            'klearDispatch',
            new Zend_Controller_Router_Route(
                'klear/dispatch/:file/*',
                array(
                    'controller' => 'index',
                    'action' => 'dispatch',
                    'module' => 'klear'
                )
            )
        );
    }

    protected function _initAssetRoutes()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $router = $frontController->getRouter();

        $router->addRoute(
            'klearScripts',
            new Zend_Controller_Router_Route_Regex(
                '(default|klear[^/]*)/js/(.*)$',
                array(
                    'controller' => 'assets',
                    'action' => 'js',
                    'module' => 'klear'
                ),
                array(
                    2 => 'file',
                    1 => 'moduleName'
                )
            )
        );

        $router->addRoute(
            'klearCss',
            new Zend_Controller_Router_Route_Regex(
                '(default|klear[^/]*)/css/(.*)$',
                array(
                    'controller' => 'assets',
                    'action' => 'css',
                    'module' => 'klear'
                ),
                array(
                    2 => 'file',
                    1 => 'moduleName'
                )
            )
        );

        $router->addRoute(
            'klearCssExtended',
            new Zend_Controller_Router_Route_Regex(
                '(default|klear[^/]*)/css-extended/(.*)/(.*)$',
                array(
                    'controller' => 'assets',
                    'action' => 'css-extended',
                    'module' => 'klear'
                ),
                array(
                    3 => 'file',
                    2 => 'plugin',
                    1 => 'moduleName'
                )
            )
        );

        /*
         * TODO: Preparar la expresión regular para que soporte más tipos de imágenes
         */
        $router->addRoute(
            'klearCssImages',
            new Zend_Controller_Router_Route_Regex(
                '(default|klear[^/]*)/css/(.*\.png)$',
                array(
                    'controller' => 'assets',
                    'action' => 'css-image',
                    'module' => 'klear'
                ),
                array(
                    2 => 'file',
                    1 => 'moduleName'
                )
            )
        );

        $router->addRoute(
            'klearImages',
            new Zend_Controller_Router_Route_Regex(
                '(default|klear[^/]*)/images/(.*)$',
                array(
                    'controller' => 'assets',
                    'action' => 'image',
                    'module' => 'klear'
                ),
                array(
                    2 => 'file',
                    1 => 'moduleName'
                )
            )
        );

        $router->addRoute(
            'klearBinaryAssets',
            new Zend_Controller_Router_Route_Regex(
                '(default|klear[^/]*)/bin/(.*)$',
                array(
                    'controller' => 'assets',
                    'action' => 'bin',
                    'module' => 'klear'
                ),
                array(
                    2 => 'file',
                    1 => 'moduleName'
                )
            )
        );
    }

    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Klear',
            'basePath'  => __DIR__,
        ));

        $autoloader->addResourceType('actionhelpers', 'controllers/helpers/', 'Controller_Helper');

        Zend_Controller_Action_HelperBroker::addPath(
            __DIR__ . '/controllers/helpers',
            'Klear_Controller_Helper_'
        );

        return $autoloader;
    }
}
