<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_Section  implements \IteratorAggregate
{
    protected $_iden;

    protected $_name;

    protected $_class;

    protected $_meta;
     
    protected $_description;

    protected $_showOnlyIf = true;

    protected $_notMultilangPropertyKeys = array();

    protected $_menu = null;

    protected $_subsections = array();

    protected $_skip = array();
    
    protected $_default = false;

    public function getIterator()
    {
        return new \ArrayIterator($this->_subsections);
    }

    public function setIden($iden)
    {
        $this->_iden = $iden;
        return $this;
    }

    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function setMeta($meta)
    {
        $this->_meta = $meta;
        return $this;
    }
    
    public function setParentMenu($menu)
    {
        $this->_menu = $menu;
        return $this;
    }
    /*
     * skip subsections
     */
    public function setDataToSkip($skip)
    {
        $this->_skip = $skip;
        return $this;
    }

    public function setData(Zend_Config $data)
    {

        $config = new Klear_Model_ConfigParser();
        $config->setConfig($data);

        $this->_name = $config->getRequiredProperty("title");
        $this->_description = $config->getProperty("description");
        $this->_meta = $config->getProperty("meta");
        
        if ($config->exists("showOnlyIf")) {
            $this->_showOnlyIf = (bool)$config->getProperty("showOnlyIf");
        }

        $this->_class = $config->getProperty("class");
        $this->_default = (bool)$config->getProperty("default");

        
        if (!isset($data->submenus) ||
                empty($data->submenus) ||
                    !$this->_showOnlyIf) {
            return;
        }

        foreach ($data->submenus as $file => $sectionData) {


            if (in_array($file, $this->_skip)) continue;
            
            $subsection = new Klear_Model_SubSection;

            $subsection
                ->setParentMenu($this->_menu)
                ->setMainFile($file)
                ->setData($sectionData);


            if ($subsection->_hasAccess() &&
                    $subsection->isShowable()) {

                $this->_subsections[] = $subsection;
            }
        }
    }

    public function isShowable()
    {
        return $this->_showOnlyIf;
    }
    
    public function getIden()
    {
        return $this->_iden;
    }

    public function getMeta()
    {
        return $this->_meta;
    }
    
    public function getName()
    {
        return Klear_Model_Gettext::gettextCheck($this->_name);
    }

    public function getClass()
    {
        return $this->_class;
    }

    public function getDescription()
    {
        return Klear_Model_Gettext::gettextCheck($this->_description);
    }

    protected function _hasAccess()
    {
        return true;
    }

    public function hasSubsections()
    {
        return count($this->_subsections) > 0;
    }

    public function getSubsections()
    {
        return $this->_subsections;
    }
}
