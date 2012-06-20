<?php

class Klear_IndexController extends Zend_Controller_Action
{

    protected $_auth;
    protected $_loggedIn = false;

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->ContextSwitch()
                ->addActionContext('hello', 'json')
                ->addActionContext('bye', 'json')
                ->addActionContext('registertranslation', 'json')
                ->initContext('json');

        $this->_auth = Zend_Auth::getInstance();
    }

    public function indexAction()
    {
        // action body
    }


    public function byeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::forgetMe();

        if ((bool)$this->getRequest()->getParam('json', false)) {
            $jsonResponse = new Klear_Model_SimpleResponse();
            $jsonResponse->setData(
                array(
                    'success'=> true
                )
            );
            $jsonResponse->attachView($this->view);
        } else {
            $this->_helper->redirector('index');
        }
    }

    public function helloAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        /*
         * Si el plugin de autenticación ha dejado en request
         * algún mensaje de error, se lo dejamos en flashMessenger
         * para que lo recoja loginController al re-servir el formulario
         */
        if ($this->getRequest()->getParam("loginError")) {
            $this->_helper->getHelper('FlashMessenger')->addMessage($this->getRequest()->getParam("loginError"));
        }

        $jsonResponse = new Klear_Model_SimpleResponse();
        $jsonResponse->setData(
            array(
                'success'=> true
            )
        );
        $jsonResponse->attachView($this->view);
    }


    public function registertranslationAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $namespace = $this->getRequest()->getParam("namespace", false);
        $str = $this->getRequest()->getParam("str", false);

        if ($namespace && $str) {
            $translationFile = $this->_getTranslationFilePath($namespace);
            $jsTranslations = include($translationFile);

            if (!in_array($str, $jsTranslations)) {
                $jsTranslations[] = $str;
                $this->_writeTranslationsFile($translationsFile, $jsTranslations);
            }
        }

        $jsonResponse = new Klear_Model_SimpleResponse();
        $jsonResponse->setData(
            array(
                'success'=> true,
            )
        );
        $jsonResponse->attachView($this->view);
    }

    protected function _getTranslationFilePath($jsNamespace)
    {
        $module = $this->_getNamespaceModuleName($jsNamespace);
        $this->getRequest()->setModuleName($module);

        $translationFile = implode(
            DIRECTORY_SEPARATOR,
            array(
                $this->getFrontController()->getModuleDirectory(),
                'languages',
                'js-translations.php'
            )
        );

        if (!file_exists($translationFile)) {
            $this->_writeTranslationsFile($translationFile, array());
        }

        return $translationFile;
    }

    protected function _getNamespaceModuleName($jsNamespace)
    {
        list($type, $namespace) = explode("/", $jsNamespace, 2);
        if ($namespace == 'null') {
            $namespace = 'default.default';
        }
        list($module, $plugin) = explode(".", $namespace, 2);

        $modules = Zend_Controller_Front::getInstance()->getControllerDirectory();
        $mods = array_keys($modules);

        foreach ($mods as $mod) {
            if (strtolower($mod) == strtolower($module)) {
                return $mod;
            }
        }
        return 'default';
    }

    protected function _writeTranslationsFile($translationFile, $contents)
    {
        $fileContents = "<?php\n\n";
        $fileContents .= "return " . var_export($contents, true) . ";\n";
        file_put_contents($translationFile, $fileContents);
    }

    /**
     * Esta acción redirigirá a la acción del módulo de subsección definido en el fichero de
     *  configuración de sección
     */
    public function dispatchAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $file = $this->getRequest()->getParam("file");

        $filePath = 'klear.yaml://' . $file;


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

        // Cargamos el configurador de secciones por defecto
        $sectionConfig = new Klear_Model_SectionConfig;
        $sectionConfig->setConfig($config);
        if (!$sectionConfig->isValid()) {
            throw new Zend_Controller_Action_Exception($this->view->translate("Configuration error"));
            return;
        }

        if (!$this->_auth->hasIdentity()) {
            $this->_forward("hello", "index", "klear");
            return;
        }

        // Nos devuelve el configurador del módulo concreto instanciado.
        $moduleConfig = $sectionConfig->factoryModuleConfig();

        $moduleConfig->setConfig($config);

        $moduleRouter = $moduleConfig->buildRouterConfig();
        $moduleRouter->setParams($this->getRequest()->getParams());


        $moduleRouter->resolveDispatch();

        //Así tendremos disponible la configuración del módulo en el controlador principal.
         $this->_forward(
             $moduleRouter->getActionName(),
             $moduleRouter->getControllerName(),
             $moduleRouter->getModuleName(),
             array(
                 "mainRouter"=>$moduleRouter,
             )
         );
    }
}

