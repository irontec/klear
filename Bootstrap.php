<?php

class Klear_Bootstrap extends Zend_Application_Module_Bootstrap
{
	
    
	/**
	 * Registramos plugin que se encarga de discriminar cargas desde fuera del módulo
	 */
	protected function _initKlear()
	{
	
		$front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Klear_Plugin_Init());
        
	}
	
	protected function _initJsRoutes()
	{
		
		$frontController = Zend_Controller_Front::getInstance();
		$router = $frontController->getRouter();
		
		$router->addRoute(
			'klearScripts',
			$route = new Zend_Controller_Router_Route_Regex(
						'(klear[^/]*)/js/(.*)$',
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
				$route = new Zend_Controller_Router_Route_Regex(
						'(klear[^/]*)/css/(.*)$',
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
				'klearImagesinCss',
				$route = new Zend_Controller_Router_Route_Regex(
						'(klear[^/]*)/css/(.*\.png)$',
						array(
								'controller' => 'assets',
								'action' => 'imageCss',
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
				$route = new Zend_Controller_Router_Route_Regex(
						'(klear[^/]*)/images/(.*)$',
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
	
	}
}
