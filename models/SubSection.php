<?php

/**
 * Clase factory de todos los objetos a partir de klear[config]
* @author jabi
*
*/
class Klear_Model_SubSection extends Klear_Model_Section
{
    protected $_mainFile;
    
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
