<?php

class Klear_View_Helper_SiteConfig extends Klear_View_Helper_Base
{
    /**
     * @return Klear_Model_Menu
     */
    public function SiteConfig()
    {
        return $this->_klearBootstrap->getOption('siteConfig');
    }
}
