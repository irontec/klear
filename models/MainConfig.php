<?php

/**
 * Clase factory de todos los objetos a partir de klear[config] 
 * @author jabi
 *
 */
class Klear_Model_MainConfig {
	
	protected $_siteConfig;
	protected $_menu;
	
	/**
	 * @param Zend_Config $config Fichero configuración klear
	 */
	public function setConfig(Zend_Config $config) {
		$this->_buildSiteConfig($config);
		$this->_buildMenu($config);		
	}

	
	protected function _buildSiteConfig(Zend_Config $config) {
		$this->_siteConfig = new Klear_Model_SiteConfig;
		$this->_siteConfig->setConfig($config->main);
	}

	protected function _buildMenu(Zend_Config $config) {
	    $this->_menu = new Klear_Model_Menu;
		$this->_menu->setConfig($config->menu);
		$this->_menu->setSiteConfig($this->getSiteConfig());
		$this->_menu->parse();
		
	}
	
	/**
	 * Factory que devuelve COnfiguracion general de Klear en base a sección "main" del fichero
	 * @return Klear_Model_SiteConfig
	 */
	public function getSiteConfig() {
		return $this->_siteConfig;
	}
	
	/**
	 * Factory que devuelve el objeto menu a partir de la config de klear
	 * @return Klear_Model_Menu
	 */
	public function getMenu() {
		return $this->_menu;
	}
	
}