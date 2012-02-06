<?php

/**
 * Clase respuesta para peticiones desde klear.request.js
 * Peticiones de tipo screen / dialog
 *
 * @author jabi
 *
 */
class Klear_Model_DispatchResponse {

	const RESPONSE_TYPE = 'dispatch';

	protected $_jsFiles = array();
	protected $_cssFiles = array();
	protected $_templates = array();
	protected $_module;
	protected $_plugin;
	protected $_data;


	public function addTemplate($tmpl, $iden = false) {
	    
	    $iden = ($iden)? $iden : crc32($tmpl);
		$this->_templates[$iden] = $tmpl;
	}

	public function addTemplateArray($aTmpls) {
	    $this->_templates += $aTmpls;
	}

	public function addJsFile($js) {
		$this->_jsFiles[crc32($js)] = $js;
	}

	public function addJsArray($aJs) {
	    $this->_jsFiles += $aJs;
	}
	
	public function addCssFile($css) {
		$this->_cssFiles[crc32($css)] = $css;
	}

	public function addCssArray($aCss) {
	    $this->_cssFiles += $aCss;
	}
	
	public function setData($data) {
		$this->_data = $data;
	}

	public function setModule($module) {
		$this->_module = $module;
	}

	public function setPlugin($plugin) {
		$this->_plugin = $plugin;
	}

	public function attachView(Zend_View $view) {
		$view->baseurl = $view->baseUrl($this->_module);
		$view->templates = $this->_templates;
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