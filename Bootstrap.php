<?php

class Klear_Bootstrap extends Zend_Application_Module_Bootstrap
{

    protected $_configFile;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct($application)
    {
        $this->_init();
        return parent::__construct($application);
    }

    protected function _init()
    {
        $this->_configFile = APPLICATION_PATH . '/configs/klear/klear.yaml';
    }

    protected function _initJson()
    {
        // Hasta que se resuelve el tema de que Zend_config JSON_encodee bien....
        Zend_Json::$useBuiltinEncoderDecoder = false;
    }

    protected function _initYamlWrapper()
    {
        $existed = in_array("klear.yaml", stream_get_wrappers());
        if (!$existed) {
            stream_wrapper_register("klear.yaml", "Klear_Model_YamlStream");
        }
    }


    /**
     * Registramos los plugins necesarios para el correcto funcionamiento de Klear
     */
    protected function _initKlear()
    {

        $front = \Zend_Controller_Front::getInstance();
        $this->setOptions(array("configFilePath"=>$this->_configFile));

        $front->registerPlugin(new Klear_Plugin_Error());
        $front->registerPlugin(new Klear_Plugin_Cache());

        $front->registerPlugin(new Klear_Plugin_Layout());


        $front->registerPlugin(new Klear_Plugin_ParserFast());
        $front->registerPlugin(new Klear_Plugin_Browser());
        $front->registerPlugin(new Klear_Plugin_Log());
        $front->registerPlugin(new Klear_Plugin_Auth());

        $front->registerPlugin(new Klear_Plugin_Parser());
        $front->registerPlugin(new Klear_Plugin_Config());
        $front->registerPlugin(new Klear_Plugin_Hooks());

        $front->registerPlugin(new Klear_Plugin_MagicCookie());
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
            'klearTemplate',
            new Zend_Controller_Router_Route_Regex(
                '(default)/template/(.*)$',
                array(
                    'controller' => 'assets',
                    'action' => 'template',
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
            'klearThemes',
            new Zend_Controller_Router_Route_Regex(
                '(default|klear[^/]*)/themes/(.*)$',
                array(
                    'controller' => 'assets',
                    'action' => 'theme',
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

        $router->addRoute(
            'klearTranslations',
            new Zend_Controller_Router_Route_Regex(
                '(default|klear[^/]*)/js/translation/(.*)$',
                array(
                    'controller' => 'assets',
                    'action' => 'js-translation',
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
        $autoloader = new Zend_Application_Module_Autoloader(
            array(
                'namespace' => 'Klear',
                'basePath'  => __DIR__,
            )
        );

        $autoloader->addResourceType('actionhelpers', 'controllers/helpers/', 'Controller_Helper');
        $autoloader->addResourceType('adapters', 'adapters/auth/', 'Auth_Adapter');
        $autoloader->addResourceType('exceptions', 'exceptions/', 'Exception');

        Zend_Controller_Action_HelperBroker::addPath(
            __DIR__ . '/controllers/helpers',
            'Klear_Controller_Helper_'
        );

        return $autoloader;
    }

}
