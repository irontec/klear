<?php

/**
 * @author lander
 */


class Klear_Model_HeaderMenu implements Iterator
{

    protected $_siteConfig;
    protected $_menuConfig;
    protected $_sections = array();
    protected $_position = 0;

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setDescription($description)
    {
        $this->_description = $description;
    }

    public function __construct()
    {
        $this->_position = 0;
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function current()
    {
        return $this->_sections[$this->_position];
    }

    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        ++$this->_position;
    }

    public function valid()
    {
        return isset($this->_sections[$this->_position]);

    }

    public function setConfig(Zend_Config $config)
    {
        $this->_config = $config;
    }

    public function setMenuConfig(Zend_Config $config)
    {
        $this->_menuConfig = $config;
    }


    public function getCurrentLang()
    {
        return $this->_siteConfig->getLang();
    }

    public function setSiteConfig(Klear_Model_SiteConfig $siteConfig)
    {
        $this->_siteConfig = $siteConfig;
    }

    protected function _identify($configKey)
    {
        $method = '_parse'. ucfirst($configKey);
        if (method_exists($this, $method)) {
            $this->{$method}($configKey);
            return true;
        }
        return false;
    }

    protected function _parseKlearSettings($configKey)
    {
        $section = new Klear_Model_Section;
        $section
        ->setParentMenu($this)
        ->setName('')
        ->setData(new Zend_Config(array('title'=>'')));
        $this->_sections[] = $section;




    }

    protected function _parseKlearMenuLink($configKey)
    {
        $sections = array();
        foreach ($this->_config->$configKey as $section=>$subSection) {
            if ($subSection instanceof Zend_Config) {
                $sections[$section] = array();
                foreach ($subSection as $subSectionKey=>$bool) {
                    $sections[$section][] = $subSectionKey;
                }
            } else {
                $sections[$section] = array();
            }
        }


        foreach ($this->_menuConfig as $name => $sectionData) {
            if (array_key_exists($name, $sections)) {
                $skip = array();
                if (! empty($sections[$name])) {
                    foreach ($sectionData->submenus as $submenuIndex => $submenu) {
                        if (!in_array($submenuIndex, $sections[$name])) {
                            $skip[] = $submenuIndex;
                        }
                    }
                }
                $section = new Klear_Model_Section;
                $section
                ->setParentMenu($this)
                ->setName($name)
                ->setDataToSkip($skip)
                ->setData($sectionData);
                $this->_sections[] = $section;
            }
        }
    }

    public function parse()
    {
        foreach ($this->_config as $name => $sectionData) {
            if ($this->_identify($name)) {
                continue;
            }
            $section = new Klear_Model_Section;

            $section
                ->setParentMenu($this)
                ->setName($name)
                ->setData($sectionData);

            $this->_sections[] = $section;

        }

        $this->_config = null;
    }




}