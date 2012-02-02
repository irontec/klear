<?php

class Klear_Bootstrap extends Zend_Application_Module_Bootstrap
{
	/**
	 * Registramos el plugin que se encarga de inicializar Klear
	 * solo para los módulos de Klear
	 */
	protected function _initKlear()
	{
		$front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Klear_Plugin_Init());
        /*
         * 
         */
        $front->registerPlugin(new Klear_Plugin_I18n());
	}
	
	protected function _initModuleRoutes()
	{
	    $frontController = Zend_Controller_Front::getInstance();
	    $router = $frontController->getRouter();
	    
	    $router->addRoute(
	            'klearDispatch',
	            $route = new Zend_Controller_Router_Route(
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

		/*
		 * TODO: Preparar la expresión regular para que soporte más tipos de imágenes
		 */
		$router->addRoute(
	        'klearCssImages',
	        $route = new Zend_Controller_Router_Route_Regex(
                '(klear[^/]*)/css/(.*\.png)$',
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
