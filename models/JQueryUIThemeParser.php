<?php

class Klear_Model_JQueryUIThemeParser
{

    const filename = 'jquery-ui-themes.yaml';

    protected $_config;

    protected $_localConfig = false;
    protected $_localBaseUrl = false;

    public function setLocalExtraConfigFile($localFile)
    {
        $localFile = APPLICATION_PATH
                   . DIRECTORY_SEPARATOR
                   .  $localFile;

        if (file_exists($localFile)) {

            $cache = $this->_getCache($localFile);
            $this->_localConfig = $cache->load(md5($localFile));

            if (!$this->_localConfig) {

                $this->_localConfig = new Zend_Config_Yaml(
                    $localFile,
                    APPLICATION_ENV,
                    array(
                        "yamldecoder" => "yaml_parse"
                    )
                );

                $cache->save($this->_localConfig);
            }

            $this->_localBaseUrl = $this->_localConfig->baseurl;
        }
    }

    protected function _getCache($filePath)
    {
        $cacheManager = Zend_Controller_Front::getInstance()
        ->getParam('bootstrap')
        ->getResource('cachemanager');

        $cache = $cacheManager->getCache('klearconfig');
        $cache->setMasterFile($filePath);
        return $cache;
    }

    public function init()
    {
        $front = Zend_Controller_Front::getInstance();

        $cssAssetsPath = array(
            $front->getModuleDirectory('klear'),
            'assets',
            'css',
            self::filename
        );
        $cssAssetsPath = implode(DIRECTORY_SEPARATOR, $cssAssetsPath);

        if (!file_exists($cssAssetsPath)) {

            Throw new Zend_Exception("No existe el fichero de configuración de estilos (jQuery UI)");
        }

        $cache = $this->_getCache($cssAssetsPath);
        $this->_config = $cache->load(md5($cssAssetsPath));

        if (!$this->_config) {
            $this->_config = new Zend_Config_Yaml(
                $cssAssetsPath,
                APPLICATION_ENV,
                array(
                    "yamldecoder" => "yaml_parse"
                )
            );

            $cache->save($this->_config);
        }

    }


    protected function _parseForTheme(Zend_Config $config, $targetTheme)
    {
        foreach ($config->themes as $_theme) {
            if ($targetTheme === trim($_theme)) {
                $themePath = str_replace('%theme%', $_theme, $config->baseurl);
                return $themePath;
            }
        }

        return false;
    }

    public function getPathForTheme($theme)
    {


        if (isset($this->_config->multicdn)) {
            foreach ($this->_config->multicdn as $cdnConfig) {
                $foundPath = $this->_parseForTheme($cdnConfig, $theme);
                if ($foundPath !== false) {
                    return $foundPath;
                }
            }
        }

        // old school config jquery-ui themes config file (no multicdn)
        if (isset($this->_config->themes)) {
            $foundPath = $this->_parseForTheme($config, $theme);
            if ($foundPath !== false) {
                return $foundPath;
            }
        }

        if (false !== $this->_localConfig) {

            foreach ($this->_localConfig->themes as $_theme) {

                if ($theme === trim($_theme)) {
                    return
                        str_replace('%theme%', $_theme, $this->_localBaseUrl);
                }
            }
        }

        Throw new Zend_Exception("No existe una configuración de estilos válida");
    }
}