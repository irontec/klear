<?php

class Klear_View_Helper_Info extends Klear_View_Helper_Base
{

    /**
     * @return Klear_Model_Menu
     */
    public function Info()
    {

        $siteConfig = $this->_klearBootstrap->getOption('siteConfig');

        $siteName = $siteConfig->getSiteName();
        $subName = $siteConfig->getSiteSubName();

        return '<h1>' . $siteName . '</h1>'  .
                '<h2>' . $subName . '</h2>';
    }
}