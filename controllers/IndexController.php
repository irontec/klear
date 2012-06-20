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
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Layout::getMvcInstance()->disableLayout();
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
        // action body

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        /* Si el plugin de autenticación ha dejado en request
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
        // action body

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $namespace = $this->getRequest()->getParam("namespace", false);
        $str = $this->getRequest()->getParam("str", false);

        if ($namespace && $str) {
            list($type, $namespace) = explode("/", $this->getRequest()->getParam("namespace"), 2);
            if ($namespace=='null') {
                $namespace = 'default.default';
            }
            if (strpos($namespace, ".")) {
                list($module, $plugin) = explode(".", $namespace, 2);
            } else {
                $plugin = null;
                $module = $namespace;
            }
            list($module, $plugin) = explode(".", $namespace, 2);
            $modules = Zend_Controller_Front::getInstance()->getControllerDirectory();
            $mods = array_keys($modules);
            $done = false;

            foreach ($mods as $mod) {
                if (strtolower($mod) == strtolower($module)) {
                    $this->getRequest()->setModuleName($mod);
                    $done = true;
                    break;
                }
            }
            if ($done === false) {
                $this->getRequest()->setModuleName('default');
            }
            $bootstrap = $this->getFrontController()->getParam("bootstrap");
            $klearBootstrap = $bootstrap->getResource('modules')->offsetGet('klear');
            $siteLanguage = $klearBootstrap->getOption('siteConfig')->getLang();


            $translationFile = implode(
                DIRECTORY_SEPARATOR,
                array(
                    $this->getFrontController()->getModuleDirectory(),
                    'languages',
                    'js-translations.php'
                )
            );

            if (!file_exists($translationFile)) {
                $contents = array();
                $fileContents = "<?php\n\n";
                $fileContents .= "return " . var_export($contents, true) . ";\n";
                file_put_contents($translationFile, $fileContents);
            }

            $jsTranslations = array();
            $jsTranslations = include($translationFile);

            if (!in_array($str, $jsTranslations)) {
                $jsTranslations[] = $str;
                $fileContents = "<?php\n\n";
                $fileContents .= "return " . var_export($jsTranslations, true) . ";\n";
                file_put_contents($translationFile, $fileContents);

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

