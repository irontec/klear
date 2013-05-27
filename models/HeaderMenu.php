<?php
/**
 * @author lander
 */
class Klear_Model_HeaderMenu extends Klear_Model_Menu
{
    protected $_menuConfig;

    public function setMenuConfig(Zend_Config $config)
    {
        $this->_menuConfig = $config;
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

    protected function _identify($configKey)
    {
        $method = '_parse'. ucfirst($configKey);
        if (method_exists($this, $method)) {
            $this->{$method}($configKey);
            return true;
        }
        return false;
    }

    /**
     * (called from _identify)
     * @param unknown_type $configKey
     */
    protected function _parseKlearSettings()
    {
        $section = new Klear_Model_Section;
        $section
            ->setParentMenu($this)
            ->setName('')
            ->setData(new Zend_Config(array('title'=>'')));
        $this->_sections[] = $section;
    }

    /**
     * (called from _identify)
     * @param unknown_type $configKey
     */
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
                if (!empty($sections[$name])) {
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
}