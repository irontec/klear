<?php
/**
 * Plugin encargado de inicializar ambas versiones de klear.yaml
 *
 */
class Klear_Plugin_ParserFast extends Klear_Plugin_Parser
{
    protected $_bootstrapConfigIden = 'klearBaseConfigFast';
    
    protected function _initPlugin()
    {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap')->getResource('modules')->offsetGet('klear');
        
        $this->_configFilePath = $this->_bootstrap->getOption("configFilePath");
        $this->_filePath = $this->_configFilePath;
    }
    
    protected function _getCacheKey()
    {
        return md5($this->_filePath);
    }
    
    
}
