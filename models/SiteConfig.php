<?php

class Klear_Model_SiteConfig
{
    protected $_year;
    protected $_sitename;
    protected $_sitesubname;
    protected $_timezone;
    protected $_signature = 'Klear :: Irontec';
    protected $_defaultCustomConfiguration;

    protected $_logo;
    protected $_favIcon;

    protected $_disableMinifiers = false;
    protected $_disableAssetsCache = false;
    protected $_disableCDN = false;

    protected $_rawJavascripts = array();
    protected $_rawCss = array();

    protected $_cssExtended;
    protected $_actionHelpers = array();

    // En caso de disponer de las dos variables en klear.yaml, custom tiene más peso

    // Nombre del tema de jQUeryUI especificado en klear/assets/css/jquery-ui-themes.yaml (google CDN)
    protected $_jqueryUIPathTheme;

    // Ruta de la aplicación (public), hacia el tema custom de jQuery UI
    protected $_jqueryUICustomTheme;

    protected $_currentTheme;

    protected $_themeRoller = array();
    protected $_themeRollerCustom = array();

    protected $_lang;
    protected $_langs = array();
    
    protected $_modelLangs = array();

    protected $_authConfig = false;

    protected $_rememberScroll;

    protected $_optionalParams = array(
            'logo',
            'favIcon',
            'disableMinifier',
            'disableAssetsCache',
            'cssExtended',
            'actionHelpers',
            'disableCDN',
            'signature',
            'defaultCustomConfiguration',
            'rememberScroll'
    );

    protected $_session;

    protected $_requiredParams = array(
            'year',
            'sitename'
    );

    public function __construct(Zend_Config $config = null)
    {
        if (!is_null($config)) {
            $this->setConfig($config);
        }
    }


    public function setConfig(Zend_Config $config)
    {
        $this->_initRequiredParams($config);
        $this->_initOptionalParams($config);

        $this->_initJQueryUITheme($config);
        $this->_initKlearLanguage($config);
        $this->_initTimezone($config);

        $this->_initSiteSubName($config);

        if (isset($config->auth)) {
            $this->_initAuthConfig($config->auth);
        }

        $this->_initRawIncludes($config);
        $this->_initDynamicClass($config);
    }

    public function setConfigForAuth(Zend_Config $config)
    {
        if (isset($config->auth)) {
            $this->_initAuthConfig($config->auth);
        }
        $this->_initDynamicClass($config);
    }

    protected function _initAuthConfig(Zend_Config $authConfig)
    {
        $this->_authConfig = new Klear_Model_ConfigParser();
        $this->_authConfig->setConfig($authConfig);
    }

    protected function _initSiteSubName(Zend_Config $config)
    {

        if ($config->sitesubname) {
            $subNameConfig = new Klear_Model_ConfigParser();
            $subNameConfig->setConfig($config);
            $this->_sitesubname = $subNameConfig->getProperty("sitesubname");
        }


    }

    protected function _initRequiredParams(Zend_Config $config)
    {
        foreach ($this->_requiredParams as $param) {
            if (!isset($config->$param)) {
                throw new Klear_Exception_MissingConfiguration($param .  ' config is required');
            }
            $this->{'_' . $param} = $config->$param;
        }
        return $this;
    }

    protected function _initOptionalParams(Zend_Config $config)
    {
        foreach ($this->_optionalParams as $param) {
            if (isset($config->$param) && $config->$param !== '') {
                $this->{'_' . $param} = $config->$param;
            }
        }
        return $this;
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
        
        /*
         * Loading Model Languages
         */
        if (isset($config->modelLangs)) {
            foreach ($config->modelLangs as $_langIden => $lang) {
                $modelLanguage = new Klear_Model_Language();
                $modelLanguage->setIden($_langIden);
                $modelLanguage->setConfig($lang);
                $this->_modelLangs[$modelLanguage->getIden()] = $modelLanguage;
            }
        }

        // Klear_Model_Interface_Language_Filter
        /*
         * Resquested Language // SESSION Language
        */

        if (!$this->_session instanceof Zend_Session_Namespace) {
            $this->_session = new Zend_Session_Namespace('UserSettings');
        }

        $front = Zend_Controller_Front::getInstance();

        $requestedLanguage = $front->getRequest()->getParam('language', false);

        $lang = null;

        if ($requestedLanguage && (array_key_exists($requestedLanguage, $this->_langs)) ) {
            $lang = $requestedLanguage;
        }
        if ((!$lang)
                && ($this->_session->currentSystemLanguage!=null)
                && (array_key_exists($this->_session->currentSystemLanguage, $this->_langs)) ) {
            $lang = $this->_session->currentSystemLanguage;
        }

        if (!$lang) {
            $lang = $config->lang;
        }

        $this->_session->currentSystemLanguage = $lang;

        /*
         * Setting language Object
        */
        $this->_lang = $this->_langs[$this->_session->currentSystemLanguage];

        Zend_Registry::set('currentSystemLanguage', $this->_lang);
        Zend_Registry::set('SystemDefaultLanguage', $this->_langs[$config->lang]);
        Zend_Registry::set('defaultLang', $this->_lang->getIden());
        Zend_Registry::set('SystemLanguages', $this->_langs);

    }

    public function _initTimezone(Zend_Config $config)
    {
        if ($config->timezone) {
            $this->_timezone = $config->timezone;
            date_default_timezone_set($this->_timezone);
        } else {
            throw new Exception("Timezone not specified in klear.yaml.");
        }
    }

    public function _initRawIncludes(Zend_Config $config)
    {
        if (!isset($config->raw)) {
            return;
        }

        if (isset($config->raw->javascript)) {
            foreach ($config->raw->javascript as $script) {
                $this->_rawJavascripts[] = $script;
            }
        }

        if (isset($config->raw->css)) {
            foreach ($config->raw->css as $css) {
                $this->_rawCss[] = $css;
            }
        }
    }

    protected function _initThemeRoller(Zend_Config $config)
    {
        if (isset($config->jqueryUI->themeRoller)) {
            if (isset($config->jqueryUI->themeRoller->themes)) {
                $themeParser = new Klear_Model_JQueryUIThemeParser;
                $themeParser->init();
                foreach ($config->jqueryUI->themeRoller->themes as $theme) {
                    if (isset($config->jqueryUI->jqueryUI->extraThemeFile)) {
                        $themeParser->setLocalExtraConfigFile($config->jqueryUI->extraThemeFile);
                    }
                    $this->_themeRoller[$theme] = $themeParser->getPathForTheme($theme);
                }
            }
            if (isset($config->jqueryUI->themeRoller->paths)) {
                foreach ($config->jqueryUI->themeRoller->paths as $themeName => $path) {
                    $this->_themeRoller[$themeName] = $path;
                    $this->_themeRollerCustom[] = $themeName;
                }
            }
        }
    }

    public function _initJQueryUITheme(Zend_Config $config)
    {
        if (isset($config->jqueryUI)) {
            $this->_initThemeRoller($config);

            $front = Zend_Controller_Front::getInstance();
            $requestedTheme = $front->getRequest()->getParam('theme', false);

            if (!$this->_session instanceof Zend_Session_Namespace) {
                $this->_session = new Zend_Session_Namespace('UserSettings');
            }

            $configTheme = isset($config->jqueryUI->theme)? $config->jqueryUI->theme: null;

            $configThemePath = isset($config->jqueryUI->path)? $config->jqueryUI->path: null;
            if ($configThemePath && !$configTheme) {
                $configTheme = $configThemePath;
            }
            $themes = $this->getThemeRoller(null);

            if ($requestedTheme) {
                $requestedTheme = trim($requestedTheme);
                if (array_key_exists($requestedTheme, $themes)) {
                    $this->_session->theme = $requestedTheme;
                }
            }


            if ($this->_session->theme && !is_null($this->_session->theme)) {
                $configTheme = $this->_session->theme;
                if (in_array($configTheme, $this->_themeRollerCustom)) {
                    $configThemePath = $themes[$configTheme];
                }
            }



            if (!$configThemePath) {
                $themeParser = new Klear_Model_JQueryUIThemeParser;
                $themeParser->init();
                // If configured, we can pass the parser, extra custom jQueryUI themes
                if (isset($config->jqueryUI->extraThemeFile)) {
                    $themeParser->setLocalExtraConfigFile($config->jqueryUI->extraThemeFile);
                }
                $this->_jqueryUIPathTheme = $themeParser->getPathForTheme($configTheme);
                $this->_currentTheme = $configTheme;
            } else {
                $this->_jqueryUICustomTheme = $configThemePath;
                $this->_currentTheme = $configTheme;
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


        $dynamic = $dynamicClassName::factory();

        if (!is_subclass_of($dynamic, '\Klear_Model_Settings_Dynamic_Abstract')) {
            throw new Exception('Dynamic class does not extend Klear_Model_Settings_Dynamic_Abstract');
        }

        $dynamic->init($config);

        $this->_sitename = $dynamic->processSiteName($this->_sitename);
        $this->_sitesubname = $dynamic->processSiteSubName($this->_sitesubname);
        $this->_langs = $dynamic->processLangs($this->_langs);
        $this->_modelLangs = $dynamic->processModelLangs($this->_modelLangs);
        $this->_logo = $dynamic->processLogo($this->_logo);
        $this->_favIcon = $dynamic->processFavIcon($this->_favIcon);
        $this->_timezone = $dynamic->processTimezone($this->_timezone);
        $this->_jqueryUIPathTheme = $dynamic->processjQueryUI($this->_jqueryUIPathTheme);
        $this->_rawCss = $dynamic->processRawCss($this->_rawCss);
        $this->_rawJavascripts = $dynamic->processRawJavascripts($this->_rawJavascripts);
        $this->_authConfig = $dynamic->processAuthConfig($this->_authConfig);
        $this->_year = $dynamic->processYear($this->_year);
        $this->_signature = $dynamic->processSignature($this->_signature);
    }

    public function getYear()
    {
        return $this->_year;
    }

    public function getName()
    {
        return $this->_sitename;
    }

    public function getSiteName()
    {
        return $this->_sitename;
    }

    public function getSiteSubName()
    {
        return Klear_Model_Gettext::gettextCheck($this->_sitesubname);

    }

    public function getLang()
    {
        return $this->_lang;

    }

    public function getLogo()
    {
        return $this->_logo;

    }

    public function getFavIcon()
    {
        return $this->_favIcon;

    }

    public function getLangs()
    {
        if (sizeof($this->_langs) == 0) return false;
        return $this->_langs;
    }
    
    public function getModelLangs()
    {
        if (sizeof($this->_modelLangs) == 0) return false;
        return $this->_modelLangs;
    }

    public function getCurrentTheme()
    {
        return $this->_currentTheme;
    }

    public function getJQueryUItheme($baseUrl)
    {
        if (!empty($this->_jqueryUICustomTheme)) {
            return $baseUrl . $this->_jqueryUICustomTheme;
        } else {
            return $this->_jqueryUIPathTheme;
        }
    }

    public function getThemeRoller($baseUrl)
    {
        if ($baseUrl) {
            $baseUrl = $baseUrl;
        } else {
            $baseUrl = "";
        }
        $ret = $this->_themeRoller;
        if (count($this->_themeRollerCustom)>0) {
            foreach ($this->_themeRollerCustom as $trc) {
                $ret[$trc] = $baseUrl . $this->_themeRoller[$trc];
            }
        }
        return $ret;
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

    public function assetsCacheDisabled()
    {
        return $this->_disableAssetsCache;
    }

    public function minifiersDisabled()
    {
        return $this->_disableMinifiers;
    }

    public function getRawJavascripts()
    {
        return $this->_rawJavascripts;
    }

    public function getRawCss()
    {
        return $this->_rawCss;
    }

    public function getDisableCDN()
    {
        return $this->_disableCDN;
    }

    public function getSignature()
    {
        return $this->_signature;
    }

    public function getDefaultCustomConfiguration($key)
    {

        if (is_null($this->_defaultCustomConfiguration) ||
                !isset($this->_defaultCustomConfiguration->{$key})
                ) {
            return null;

        }

        return $this->_defaultCustomConfiguration->{$key};

    }

    public function getRememberScroll()
    {
        $rememberScroll = "false";
        if (is_null($this->_rememberScroll)) {
            $rememberScroll = "false";
        }
        if ($this->_rememberScroll === true) {
            $rememberScroll = "true";
        }
        if ($this->_rememberScroll == 1) {
            $rememberScroll = "true";
        }
        return $rememberScroll;
    }
}