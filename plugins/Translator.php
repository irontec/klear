<?php
/**
 * Plugin encargado de inicializar los recursos necesarios para Klear I18n
 * @author Jabi Infante
 *
 */
class Klear_Plugin_Translator extends Zend_Controller_Plugin_Abstract
{

    /**
     * @var Zend_Controller_Front
     */
    protected $_front;


    /*
     * Translator Variables
     */
    protected $_zendTranslateAdapter = 'Iron_Translate_Adapter_GettextKlear';

    protected $_zendTranslateContent = array('Zend_Translate_Content'=>'Zend_Translate_Content');

    protected $_translationFileName = '%LOCALE%.mo';
    
    protected $_translationLanguagePath = 'languages';


    protected $_directory;

    protected $_locale;

    protected $_locales = array();

    protected $_translationFile;

    protected $_translate;


    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$this->_front = Zend_Controller_Front::getInstance();

    	if (!preg_match("/^klear/", $request->getModuleName())) {
    		return;
    	}

    	$this->_initKlearTranslator();
    }

    protected function _initKlearTranslator()
    {

        $this->_zendTranslateAdapter = 'Iron_Translate_Adapter_GettextKlear';
        
        $this->_directory = $this->_front->getModuleDirectory();
        
        $bootstrap = $this->_front->getParam("bootstrap");
        
        $klearBootstrap = $bootstrap->getResource('modules')->offsetGet('klear');
        
        $siteLanguage = $klearBootstrap->getOption('siteConfig')->getLang();
        
        $siteLanguages = $klearBootstrap->getOption('siteConfig')->getLangs();
        
        //TODO user language
        
        $currentLanguage = $siteLanguage;
        
        $this->_locale = new Zend_Locale($currentLanguage->getLocale());
        
        foreach ($siteLanguages as $language) {
            $this->_locales[] = new Zend_Locale($language->getLocale());
        }
        
        $this->_translationFile = implode(
                DIRECTORY_SEPARATOR,
                array(
                        $this->_directory,
                        $this->_translationLanguagePath,
                        (string) $this->_locale,
                        str_replace('%LOCALE%', 
                                $this->_locale, 
                                $this->_translationFileName)
                        )
                ); 
        
        $this->_initZendTranslate();
       
        $writer = new Zend_Log_Writer_Stream('/tmp/klear-translation-error.log');
        $log    = new Zend_Log($writer);

        $this->_translate->setOptions(
            array(
                'log' => $log,
                'logUntranslated' => true
            )
        );
    }

    protected function _initZendTranslate()
    {
    	if (!Zend_Registry::isRegistered('Zend_Translate')) {
    		$this->_translate = new Zend_Translate(
				array(
					'adapter' => $this->_zendTranslateAdapter,
					'content' => $this->_translationFile,
				)
			);
    		Zend_Registry::set('Zend_Translate', $this->_translate);
    	} else {
    		$this->_translate = Zend_Registry::get('Zend_Translate');
    	}
    }

}