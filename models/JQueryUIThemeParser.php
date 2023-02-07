<?php

class Klear_Model_JQueryUIThemeParser
{
    final const filename = 'jquery-ui-themes.yaml';

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
            throw new Zend_Exception("No existe el fichero de configuración de estilos (jQuery UI)");
        }

        $cache = $this->_getCache($cssAssetsPath);
        $this->_config = $cache->load(md5($cssAssetsPath));

        $availablesEnvs = array("production", "testing", "development", "staging");
        $env = in_array(APPLICATION_ENV, $availablesEnvs) ? APPLICATION_ENV : "production";

        if (!$this->_config) {
            $this->_config = new Zend_Config_Yaml(
                $cssAssetsPath,
                $env,
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
            if ($targetTheme === trim((string) $_theme)) {
                $themePath = str_replace('%theme%', $_theme, (string) $config->baseurl);
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
            $foundPath = $this->_parseForTheme($this->_config, $theme);
            if ($foundPath !== false) {
                return $foundPath;
            }
        }

        if (false !== $this->_localConfig) {

            foreach ($this->_localConfig->themes as $_theme) {

                if ($theme === trim((string) $_theme)) {
                    return
                        str_replace('%theme%', $_theme, (string) $this->_localBaseUrl);
                }
            }
        }

        throw new Zend_Exception("No existe una configuración de estilos válida");
    }
}
