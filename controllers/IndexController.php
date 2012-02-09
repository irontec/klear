<?php

class Klear_IndexController extends Zend_Controller_Action
{

    protected $_auth;
    protected $_loggedIn = false;
    
    public function init()
    {
        /* Initialize action controller here */
    	$this->_helper->ContextSwitch()
    			->addActionContext('dispatch', 'json')
    			->addActionContext('hello', 'json')
    			->initContext('json');
    	
    	$this->_auth = Zend_Auth::getInstance();

    }

    public function indexAction()
    {
        // action body

    }

    public function helloAction()
    {
        // action body

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        
        $jsonResponse = new Klear_Model_SimpleResponse();
        
        $jsonResponse->setData(
            array(
                'success'=> true
            )
        );

        $jsonResponse->attachView($this->view);
    }

    /**
     * Esta acción redirigirá a la acción del módulo de subsección definido en el fichero de configuración de sección
     */
    public function dispatchAction()
    {   
        
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();

    	$configPath = APPLICATION_PATH . '/configs/klear/';

    	$file = $this->getRequest()->getParam("file");

    	// TODO: sanitize file param
    	$yamlFile = $configPath . $file . '.yaml';

    	if (!file_exists($yamlFile)) {
    		//TO-DO Throw Exception
    		throw new Zend_Controller_Action_Exception("No existe el Fichero de configuración.");
    		return;
    	}

    	/*
    	 * Carga configuración principal de klear
    	 */
    	$config = new Zend_Config_Yaml(
    			$yamlFile,
    			APPLICATION_ENV
    	);

    	// Cargamos el configurador de secciones por defecto
    	$sectionConfig = new Klear_Model_SectionConfig;
    	$sectionConfig->setConfig($config);
    	if (!$sectionConfig->isValid()) {
    		throw new Zend_Controller_Action_Exception("Configuración no válida");
    		return;
    	}
    	
    	if (!$this->_auth->hasIdentity()) {
    	    die("fok");
    	    $this->_forward(
    	            "hello",
    	            "index",
    	            "klear"
    	    );
    	    return;
    	}

    	// Nos devuelve el configurador del módulo concreto instanciado.
    	$moduleConfig = $sectionConfig->factoryModuleConfig();

    	$moduleConfig->setConfig($config);

    	// Le pasamos a la configuración del módulo la ruta de configuración por defecto.
    	$moduleConfig->setConfigPath($configPath);

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

