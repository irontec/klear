<?php

/**
 * Clase respuesta para peticiones desde klear.request.js
 * Peticiones de tipo screen / dialog
 *
 * @author jabi
 *
 */
class Klear_Model_DispatchResponse
{
    const RESPONSE_TYPE = 'dispatch';

    protected $_jsFiles = array();
    protected $_cssFiles = array();
    protected $_templates = array();
    protected $_mainTemplate = null;
    protected $_module;
    protected $_plugin;
    protected $_data;

    public function addTemplate($tmpl, $iden = false, $module = '')
    {
        if (false === $tmpl) {
            return;
        }

        if (!$iden) {
            $iden = crc32($tmpl);
        }

        if (!empty($module)) {

            $template = array(
                'module' => $module,
                'tmpl' => $tmpl
            );

            $this->_mainTemplate = $iden;

        } else {

            $template = $tmpl;
        }

        $this->_templates[$iden] = $template;
    }

    public function addTemplateArray($aTmpls)
    {
        $this->_templates += $aTmpls;
    }

    public function addJsFile($js, $module = '')
    {
        if (!empty($module)) {

            $script = array(
                'module' => $module,
                'tmpl' => $js
            );

        } else {

            $script = $js;
        }

        $this->_jsFiles['jsFile_' . crc32($js)] = $script;
    }

    public function addJsArray($aJs)
    {
        $this->_jsFiles += $aJs;
    }

    public function addCssFile($css)
    {
        $this->_cssFiles['cssFile_' . crc32($css)] = $css;
    }

    public function addCssArray($aCss)
    {
        $this->_cssFiles += $aCss;
    }

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function setModule($module)
    {
        $this->_module = $module;
    }

    public function setPlugin($plugin)
    {
        $this->_plugin = $plugin;
    }

    public function attachView(Zend_View $view)
    {
        $view->baseurl = $view->serverUrl($view->baseUrl($this->_module));
        $view->cleanBaseurl = $view->serverUrl($view->baseUrl());
        $view->templates = $this->_templates;
        $view->mainTemplate = $this->_mainTemplate;
        $view->scripts = $this->_jsFiles;
        $view->css = $this->_cssFiles;
        $view->data = $this->_data;
        $view->plugin = $this->_plugin;
        $view->responseType = self::RESPONSE_TYPE;

        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $view->mustLogIn = true;
        }
    }
}
