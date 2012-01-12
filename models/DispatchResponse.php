<?php

/**
 * Clase factory de todos los objetos a partir de klear[config] 
 * @author jabi
 *
 */
class Klear_Model_DispatchResponse {
	
	
	protected $_jsFiles;
	protected $_cssFiles;
	protected $_templates;
	protected $_module;
	protected $_data;
	
	
	public function addTemplate($tmpl) {
		$this->_templates[crc32($tmpl)] = $tmpl;
	}

	
	public function addJsFile($js) {
		$this->_jsFiles[] = $js;
	}
	
	public function addCssFile($css) {
		$this->_cssFiles[crc32($css)] = $css;
	}
	
	public function setData($data) {
		$this->_data = $data;
	}
	
	public function setModule($module) {
		$this->_module = $module;
	}
	
	public function attachView(Zend_View $view) {
		$view->baseurl = $view->baseUrl($this->_module);
		$view->templates = $this->_templates;
		$view->scripts = $this->_jsFiles;
		$view->css = $this->_cssFiles;
		$view->data = $this->_data;
		$view->module = $this->_module;

	}
	
	
	
}