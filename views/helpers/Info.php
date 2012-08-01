<?php

class Klear_View_Helper_Info extends Klear_View_Helper_Base
{

    /*
     * TODO: Sacar a un modelo fuera el parseador de variables (as seen on YamlStream)
     */
    protected function _parseVariables($data)
    {

        if (preg_match("/auth\.(.*)/", $data[1], $result)) {

            $auth = Zend_Auth::getInstance();
            if (($auth->hasIdentity()) &&
                (isset($auth->getIdentity()->{$result[1]}))) {

                return $auth->getIdentity()->{$result[1]};
            }
        }

        return '';
    }

    protected function _resolveVariables($content)
    {
        return preg_replace_callback(
                '/\$\{([^\}]*)\}/',
                array($this, '_parseVariables'),
                $content
        );
    }

    /**
     * @return Klear_Model_Menu
     */
    public function Info()
    {

        $siteConfig = $this->_klearBootstrap->getOption('siteConfig');

        $subName = $this->_resolveVariables($siteConfig->getSiteSubName());

        return '<h1>' . $siteConfig->getSiteName() . '</h1>'  .
                '<h2>' . $subName . '</h2>';
    }
}