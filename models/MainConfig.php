<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
 * @author jabi
 *
 */
class Klear_Model_MainConfig
{
    /**
     * @var Zend_Config
     */
    protected $_mainConfig;
    /**
     * @var Zend_Config
     */
    protected $_menuConfig;
    /**
     * @var Zend_Config
     */
    protected $_headerConfig;
    /**
     * @var Zend_Config
     */
    protected $_footerConfig;

    protected $_siteConfig;
    protected $_menu;
    protected $_headerMenu;
    protected $_footerMenu;

    public function __construct(Zend_Config $config = null)
    {
        if (!is_null($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * Guarda la configuración necesaria para configurar el sistema
     * @param Zend_Config $config Configuración de klear
     */
    public function setConfig(Zend_Config $config)
    {
        if (!isset($config->menu) || !$config->menu instanceof Zend_Config) {
            throw new \Zend_Exception(_('No menu configuration found in klear.yaml'));
        }
        $this->_mainConfig = $config->main;
        $this->_menuConfig = $config->menu;
        $this->_headerConfig = $config->headerMenu;
        $this->_footerConfig = $config->footerMenu;
    }


    /**
     * Factory que devuelve Configuracion general de Klear en base a sección "main" del fichero
     * @return Klear_Model_SiteConfig
     */
    public function getSiteConfig()
    {
        if (!isset($this->_siteConfig)) {
            $this->_buildSiteConfig();
        }
        return $this->_siteConfig;
    }

    protected function _buildSiteConfig()
    {
        $this->_siteConfig = new Klear_Model_SiteConfig($this->_mainConfig);
        $this->_siteConfig->setConfig($this->_mainConfig);
    }

    /**
     * Factory que devuelve el menu a partir de la config de klear
     * @return Klear_Model_Menu
     */
    public function getMenu()
    {
        if (!isset($this->_menu)) {
            $this->_buildMenu();
        }
        return $this->_menu;
    }

    protected function _buildMenu()
    {
        $this->_menu = new Klear_Model_Menu($this->_menuConfig);
        $this->_menu->setSiteConfig($this->getSiteConfig());
        $this->_menu->parse();
    }

    /**
     * Factory que devuelve el header menu a partir de la config de klear
     * @return Klear_Model_HeaderMenu
     */
    public function getHeaderMenu()
    {
        if (!isset($this->_headerMenu)) {
            $this->_buildHeaderMenu();
        }
        return $this->_headerMenu;
    }

    protected function _buildHeaderMenu()
    {
        $this->_headerMenu = new Klear_Model_HeaderMenu($this->_headerConfig);
        if ($this->_headerConfig) {
            $this->_headerMenu->setSiteConfig($this->getSiteConfig());
            $this->_headerMenu->setMenuConfig($this->_menuConfig);
            $this->_headerMenu->parse();
        }
    }

    /**
     * Factory que devuelve el footer menu a partir de la config de klear
     * @return Klear_Model_FooterMenu
     */
    public function getFooterMenu()
    {
        if (!isset($this->_footerMenu)) {
            $this->_buildFooterMenu();
        }
        return $this->_footerMenu;
    }

    protected function _buildFooterMenu()
    {
        $this->_footerMenu = new Klear_Model_FooterMenu($this->_footerConfig);
        if ($this->_footerConfig) {
            $this->_footerMenu->setSiteConfig($this->getSiteConfig());
            $this->_footerMenu->setMenuConfig($this->_menuConfig);
            $this->_footerMenu->parse();
        }
    }
}