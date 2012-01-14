<?php

class Klear_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	$this->_helper->ContextSwitch()
    			->addActionContext('dispatch', 'json')
    			->initContext('json');
    }

    public function indexAction()
    {
        // action body
    }
    
    /**
     * Esta acción redirigirá a la acción del módulo de subsección definido en el fichero de configuración de sección
     */
    public function dispatchAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$configPath = APPLICATION_PATH . '/configs/klear/'; 
    	
    	// TODO: sanitize file param
    	$yamlFile = $configPath . $this->getRequest()->getParam("file") . '.yaml';
    	
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

