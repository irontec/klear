<?php

/**
 * @author jabi
 * Iterador que devuelve todos los elementos del menu principal Sections > Subsections
 *
 * FIXME: Se están seteando propiedades que no están definidas (description, name, config)
 */
class Klear_Model_Menu implements \IteratorAggregate
{
    protected $_name;
    protected $_description;
    protected $_config;

    protected $_siteConfig;
    protected $_sections = array();

    public function __construct(Zend_Config $config = null)
    {
        if (!is_null($config)) {
            $this->setConfig($config);
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->_sections);
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setDescription($description)
    {
        $this->_description = $description;
    }

    public function setConfig(Zend_Config $config)
    {
        $this->_config = $config;
    }

    public function getCurrentLang()
    {
        return $this->_siteConfig->getLang();
    }

    public function setSiteConfig(Klear_Model_SiteConfig $siteConfig)
    {
        $this->_siteConfig = $siteConfig;
    }

    public function parse()
    {
        foreach ($this->_config as $sectionIden => $sectionData) {

            $section = new Klear_Model_Section;

            $section
                ->setParentMenu($this)
                ->setName($sectionIden)
                ->setData($sectionData);

            if ($section->hasSubsections()) {
                $this->_sections[] = $section;
            }
        }
        $this->_config = null;
    }
}
