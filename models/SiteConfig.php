<?php

class Klear_Model_SiteConfig
{
    protected $_year;
    protected $_name;
    protected $_lang;
    protected $_logo;

    // En caso de disponer de las dos variables en klear.yaml, custom tiene más peso

    // Nombre del tema de jQUeryUI especificado en klear/assets/css/jquery-ui-themes.yaml (google CDN)
    protected $_jqueryUIPathTheme;

    // Ruta de la aplicación (public), hacia el tema custom de jQuery UI
    protected $_jqueryUICustomTheme;

    protected $_cssExtended;

    protected $_actionHelpers = array();

    protected $_langs = array();

    protected $_authConfig = false;

    public function setConfig(Zend_Config $config)
    {
        // TODO: Control de errores, configuración mal seteada
        $this->_year = $config->year;
        $this->_name = $config->sitename;

        if (isset($config->logo)) {
            $this->_logo = $config->logo;
        }

        $this->_initJQueryUITheme($config);

        $this->_initCssExtended($config);

        $this->_initKlearLanguage($config);

        if (isset($config->auth)) {
            $this->_authConfig = new Klear_Model_KConfigParser();
            $this->_authConfig->setConfig($config->auth);
        }

        $this->_initActionHelpers($config);

        $this->_initDynamicClass($config);
    }


    protected function _initKlearLanguage(Zend_Config $config)
    {
        /*
         * Loading System Languages
         */
        if (isset($config->langs)) {
            foreach ($config->langs as $_langIden => $lang) {
                $language = new Klear_Model_Language();
                $language->setIden($_langIden);
                $language->setConfig($lang);
                $this->_langs[$language->getIden()] = $language;
            }
        }

        //Klear_Model_Interface_Language_Filter

        /*
         * Resquested Language // SESSION Language
         */

        $session = new Zend_Session_Namespace('UserSettings');

        $front = Zend_Controller_Front::getInstance();

        $requestedLanguage = $front->getRequest()->getParam('language', false);

        $lang = null;

        if ($requestedLanguage && (array_key_exists($requestedLanguage, $this->_langs)) ) {
            $lang = $requestedLanguage;
        }
        if ((!$lang)
                && ($session->currentSystemLanguage!=null)
                && (array_key_exists($session->currentSystemLanguage, $this->_langs)) ) {
            $lang = $session->currentSystemLanguage;
        }

        if (!$lang) {
            $lang = $config->lang;
        }

        $session->currentSystemLanguage = $lang;

        /*
         * Setting language Object
         */
        $this->_lang = $this->_langs[$session->currentSystemLanguage];

        Zend_Registry::set('currentSystemLanguage', $this->_lang);
        Zend_Registry::set('SystemDefaultLanguage', $this->_langs[$config->lang]);
        Zend_Registry::set('SystemLanguages', $this->_langs);

    }

    public function _initCssExtended(Zend_Config $config)
    {
        if ($config->cssExtended) {
            $this->_cssExtended = $config->cssExtended;
        }
    }


    public function _initActionHelpers(Zend_Config $config)
    {
        if ($config->actionHelpers) {
            $this->_actionHelpers = $config->actionHelpers;
        }
    }

    public function _initJQueryUITheme(Zend_Config $config)
    {
        if (isset($config->jqueryUI)) {

            if (isset($config->jqueryUI->path)) {
                $this->_jqueryUICustomTheme = $config->jqueryUI->path;

            } else {

                if (isset($config->jqueryUI->theme)) {

                    $themeParser = new Klear_Model_JQueryUIThemeParser;

                    $themeParser->init();

                    // If configured, we can pass the parser, extra custom jQueryUI themes
                    if (isset($config->jqueryUI->extraThemeFile)) {
                        $themeParser->setLocalExtraConfigFile($config->jqueryUI->extraThemeFile);
                    }

                    $this->_jqueryUIPathTheme = $themeParser->getPathForTheme($config->jqueryUI->theme);

                } else {

                    Throw new Zend_Exception("No existe una configuración de estilos válida");
                }
            }
        } else {
            Throw new Zend_Exception("No existe una configuración de estilos válida");
        }
    }

    protected function _initDynamicClass(Zend_Config $config)
    {
        if (!isset($config->dynamicConfigClass)) {
            return;
        }
        $dynamicClassName = $config->dynamicConfigClass;

        $dynamic = new $dynamicClassName;
        if (!is_subclass_of($dynamic, 'Klear_Model_Settings_Dynamic_Abstract')) {

            Throw new Exception('Dynamic class does not extend Klear_Model_Settings_Dynamic_Abstract');
        }

        $dynamic->init($config);

        $this->_name = $dynamic->processSiteName($this->_name);
        $this->_langs = $dynamic->processLangs($this->_langs);
        $this->_logo = $dynamic->processLogo($this->_logo);
        $this->_jqueryUIPathTheme = $dynamic->processjQueryUI($this->_jqueryUIPathTheme);

        $this->_authConfig = $dynamic->processAuthConfig($this->_authConfig);
    }

    public function getYear()
    {
        return $this->_year;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getLang()
    {
        return $this->_lang;

    }

    public function getLogo()
    {
        return $this->_logo;

    }

    public function getLangs()
    {
        if (sizeof($this->_langs) == 0) return false;
        return $this->_langs;
    }

    public function getJQueryUItheme($baseUrl)
    {
        if (!empty($this->_jqueryUICustomTheme)) {
            return $baseUrl . $this->_jqueryUICustomTheme;
        } else {
            return $this->_jqueryUIPathTheme;
        }
    }

    public function getActionHelpers()
    {
        return $this->_actionHelpers;
    }

    public function getCssExtendedConfig()
    {
        return $this->_cssExtended;
    }

    public function getAuthConfig()
    {
        return $this->_authConfig;
    }
}