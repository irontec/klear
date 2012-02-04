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
    protected $_zendTranslateAdapter = 'array';

    protected $_zendTranslateContent = array('Zend_Translate_Content'=>'Zend_Translate_Content');

    protected $_translationFileName = 'translation.php';
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

        $this->_zendTranslateAdapter = 'Iron_Translate_Adapter_Klear';

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

        $this->_translationFile = $this->_directory
        . DIRECTORY_SEPARATOR
        . $this->_translationLanguagePath
        . DIRECTORY_SEPARATOR
        . (string) $this->_locale
        . DIRECTORY_SEPARATOR
        . $this->_translationFileName;

        $this->_initZendTranslate();

        if (file_exists($this->_translationFile)) {
            $translations = include $this->_translationFile;
            if (!is_array($translations)) $translations = array();
            foreach ($translations as $key=>$value) {
                if ($value === false) {
                    unset($translations[$key]);
                }
            }
            if (empty($translations)) {
                $translations = $this->_zendTranslateContent;
            }
            $this->_translate->getAdapter()->addTranslation(
                array(
                    'content'=>$translations,
                    'locale'=>(string) $this->_locale
                )
            );
        }

        $writer = new Zend_Log_Writer_Stream('/tmp/foo.log');
        $log    = new Zend_Log($writer);

        $this->_translate->setOptions(
            array(
                'log' => $log,
                'logUntranslated' => false
          )
        );

        $adapter = $this->_translate->getAdapter();
        $adapter->setTranslationFile($this->_translationFile);
        $adapter->setDirectory($this->_directory);
        $adapter->setTranslationLanguagePath($this->_translationLanguagePath);
        $adapter->setTranslationFileName($this->_translationFileName);
        $adapter->setAvailableLocales($this->_locales);
        $adapter->setCurrentLocale($this->_locale);
    }

    protected function _initZendTranslate()
    {
    	if (!Zend_Registry::isRegistered('Zend_Translate')) {
    		$this->_translate = new Zend_Translate(
				array(
					'adapter' => $this->_zendTranslateAdapter,
					'content' => $this->_zendTranslateContent,
				)
			);
    		Zend_Registry::set('Zend_Translate', $this->_translate);
    	} else {
    		$this->_translate = Zend_Registry::get('Zend_Translate');
    	}
    }

}