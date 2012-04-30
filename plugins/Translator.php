<?php
/**
 * 
 * 
 * @author Lander Ontoria Gardeazabal <lander+dev@irontec.com>
 *
 */
class Klear_Plugin_Translator extends Zend_Controller_Plugin_Abstract
{

    /**
     * @var Zend_Controller_Front
     */
    protected $_front;

    /**
     * 
     * @var Klear_Model_Language
     */
    protected $_siteLanguage;
    
    /**
     * 
     * @var Zend_Locale
     */
    protected $_locale;
    
    protected $_directories = array();
    
    protected $_klearBootstrap;
    
    /**
     * 
     * @var Zend_Translate
     */
    protected $_translate;
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	if (!preg_match("/^klear/", $request->getModuleName())) {
    		return;
    	}

    	$this->_initKlearTranslator();
    }

    protected function _initKlearTranslator()
    {
        $this->_front = Zend_Controller_Front::getInstance();
        
        $bootstrap = $this->_front->getParam("bootstrap")->getResource('modules')->offsetGet('klear');
        
        $this->_siteLanguage = $bootstrap->getOption('siteConfig')->getLang();
        
        $this->_locale = new Zend_Locale($this->_siteLanguage->getLocale());
        
        $this->_directories[] = $this->_front->getModuleDirectory();
        
        $requestModuleDirectory = $this->_front->getModuleDirectory($this->getRequest()->getParam('moduleName'));
        
        if ( $requestModuleDirectory != $this->_front->getModuleDirectory()) {
            
            $this->_directories[] = $requestModuleDirectory;
        }
        
        foreach ($this->_directories as $moduleDirectory) {
            
            $tranlationPath = implode( DIRECTORY_SEPARATOR, array(
                    $moduleDirectory,
                    'languages',
                    (string) $this->_locale,
                    (string) $this->_locale . '.mo'
            ));

            if (!file_exists($tranlationPath)) continue;
            
            if (!Zend_Registry::isRegistered('Zend_Translate')) {
        		
                $this->_translate = new Zend_Translate(
    				array(
    					'adapter' => 'Zend_Translate_Adapter_Gettext',
    					'content' => $tranlationPath,
    				)
    			);
        		
        		Zend_Registry::set('Zend_Translate', $this->_translate);
        		
        	} else {
        	    
        	    $this->_translate->getAdapter()->addTranslation(array('content' => $tranlationPath));
        	    
        	}
        	
        }
        
     }

}