<?php

class Klear_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
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
    	
    	$yamlFile = APPLICATION_PATH . '/configs/klear/' . $this->getRequest()->getParam("file") . '.yaml';
    	
    	if (!file_exists($yamlFile)) {
    		//TO-DO Throw Exception
    		die("no existe el fichero");
    		return;
    	}
    	
    	/*
    	 * Carga configuración principal de klear
    	 */
    	$config = new Zend_Config_Yaml(
    			$yamlFile,
    			APPLICATION_ENV
    	);
    	
    	$sectionConfig = new Klear_Model_SectionConfig;
    	$sectionConfig->setConfig($config);
    	
    	if (!$sectionConfig->isValid()) {
    		throw new Zend_Controller_Action_Exception("Configuración no válida");
    		return;    		
    	}
    	
    	$moduleConfig = $sectionConfig->factoryModuleConfig();
    	$moduleConfig->setConfig($config);
    	
    	
    	$this->_forward($moduleConfig->getActionName(),$moduleConfig->getControllerName(),$moduleConfig->getModuleName());
    	
    	
    	
    }


}

