<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_SubSection extends Klear_Model_Section
{
    protected $_mainFile;
    protected $_disabledCount = false;

    public function setData(Zend_Config $data)
    {
        parent::setData($data);
        $this->_disabledCount = $data->get('disabledCount', false);
    }

        public function setMainFile($file)
    {
        //TODO: Excepción cuando no exista el fichero
        $this->_mainFile = $file;
        return $this;
    }

    public function getMainFile()
    {
        return $this->_mainFile;
    }

    public function isDefault()
    {
        return $this->_default;
    }

    public function shouldCountRows()
    {
        return $this->_disabledCount !== true;
    }

    protected function _hasAccess()
    {
        $auth = Zend_Auth::getInstance();
        
        if (!$auth->hasIdentity()) {
            return true;
        }
        
        if (!isset($auth->getIdentity()->access)) {
            return true;
        }
        
        $acl = $auth->getIdentity()->access;
        
        if ($auth->getIdentity()->getAdministrator()) {
            return true;
        }
        
        if (is_array($acl) && in_array($this->_mainFile, $acl)) {
            return true;
        } else {
            return false;
        }
    }
    
}
