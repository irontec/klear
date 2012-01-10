<?php

class Klear_View_Helper_SiteConfig extends Klear_View_Helper_Base {
	
	public function SiteConfig() {
		if ($this->_initialized === false) $this->_initHelper('siteConfig');
		return $this->_object;	
	}
}
