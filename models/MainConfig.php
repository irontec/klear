<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
 * @author jabi
 *
 */
class Klear_Model_MainConfig
{

    protected $_siteConfig;
    protected $_menu;
    protected $_headerMenu;
    protected $_footerMenu;

    /**
     * Construye las distintas configuraciones que se usarán en el sistema
     * (siteConfig, menu, headerMenu, etc...)
     * @param Zend_Config $config Configuración de klear
     */
    public function setConfig(Zend_Config $config)
    {
        $this->_buildSiteConfig($config);
        $this->_buildMenu($config);
        $this->_buildHeaderMenu($config);
        $this->_buildFooterMenu($config);
    }


    protected function _buildSiteConfig(Zend_Config $config)
    {
        $this->_siteConfig = new Klear_Model_SiteConfig;
        $this->_siteConfig->setConfig($config->main);
    }

    protected function _buildMenu(Zend_Config $config)
    {
        $this->_menu = new Klear_Model_Menu;
        $this->_menu->setConfig($config->menu);
        $this->_menu->setSiteConfig($this->getSiteConfig());
        $this->_menu->parse();

    }


    protected function _buildHeaderMenu(Zend_Config $config)
    {
        $this->_headerMenu = new Klear_Model_HeaderMenu;
        if ($config->headerMenu) {
            $this->_headerMenu->setConfig($config->headerMenu);
            $this->_headerMenu->setSiteConfig($this->getSiteConfig());
            $this->_headerMenu->setMenuConfig($config->menu);
            $this->_headerMenu->parse();
        }
    }

    protected function _buildFooterMenu(Zend_Config $config)
    {
        $this->_footerMenu = new Klear_Model_FooterMenu;
        if ($config->footerMenu) {
            $this->_footerMenu->setConfig($config->footerMenu);
            $this->_footerMenu->setSiteConfig($this->getSiteConfig());
            $this->_footerMenu->setMenuConfig($config->menu);
            $this->_footerMenu->parse();
        }
    }

    /**
     * Factory que devuelve Configuracion general de Klear en base a sección "main" del fichero
     * @return Klear_Model_SiteConfig
     */
    public function getSiteConfig()
    {
        return $this->_siteConfig;
    }

    /**
     * Factory que devuelve el menu a partir de la config de klear
     * @return Klear_Model_Menu
     */
    public function getMenu()
    {
        return $this->_menu;
    }

    /**
     * Factory que devuelve el header menu a partir de la config de klear
     * @return Klear_Model_HeaderMenu
     */
    public function getHeaderMenu()
    {
        return $this->_headerMenu;
    }

    /**
     * Factory que devuelve el footer menu a partir de la config de klear
     * @return Klear_Model_FooterMenu
     */
    public function getFooterMenu()
    {
        return $this->_footerMenu;
    }

}