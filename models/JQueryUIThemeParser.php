<?php


class Klear_Model_JQueryUIThemeParser
{
    
    const filename = 'jquery-ui-themes.yaml';
    
    protected $_config;
    
    
    public function init()
    {
        
        $front = Zend_Controller_Front::getInstance();
        $cssAssetsPath = implode(DIRECTORY_SEPARATOR, array(
                                $front->getModuleDirectory('klear'),
                                'assets',
                                'css',
                                self::filename
                            ));
        
        if (!file_exists($cssAssetsPath)) {
            
            Throw new Zend_Exception("No existe el fichero de configuración de estilos (jQuery UI)");
        } 
        
        
        $this->_config = new Zend_Config_Yaml($cssAssetsPath,APPLICATION_ENV);
        
        $this->_baseUrl = $this->_config->baseurl;
        
    }
    
    public function getPathForTheme($theme) {
        
        foreach ($this->_config->themes as $_theme) {
            
            if ($theme === trim($_theme)) {
               return str_replace('%theme%',$_theme,$this->_baseUrl); 
            }
        }
    
        Throw new Zend_Exception("No existe una configuración de estilos válida");
    }
    
}