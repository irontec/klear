<?php

class Klear_Plugin_Init extends Zend_Controller_Plugin_Abstract {

    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        $module = $request->getModuleName();
        if ($module != 'klear') return;
        
        $front = Zend_Controller_Front::getInstance();
           
        /**
         * Indicamos ruta del layout de klear
         */
        Zend_Layout::startMvc();
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayoutPath( $front->getMOduleDirectory() . '/layouts/scripts');
	    $layout->setLayout('layout');
	
        

		/*
		 * Carga configuración principal de klear
		 */
		$config = new Zend_Config_Yaml(
				APPLICATION_PATH . '/configs/klear/klear.yaml',
				APPLICATION_ENV
		);
		
		
		$klearConfig = new Klear_Model_MainConfig();
		$klearConfig->setConfig($config);

		/*
		 * Recupearmos bootstrap para usar su contenedor para guardar
		 */
		
		$bootstrap = $front = Zend_Controller_Front::getInstance()->getParam("bootstrap");
		
		$bootstrap
		        ->getResource('modules')
		        ->offsetGet('klear')
		        ->setOptions(array(
							"siteConfig"=>$klearConfig->getSiteConfig(),
							"menu"=>$klearConfig->getMenu())
		); 
        
    }
}

