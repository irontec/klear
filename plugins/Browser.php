<?php
/**
 * Inicilización plugin auth
 */
class Klear_Plugin_Browser extends Zend_Controller_Plugin_Abstract
{
    protected $_mainConfig;


    /**
     * Este método se ejecuta una vez se ha matcheado la ruta adecuada
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::routeShutdown()
     */
    public function routeShutdown(\Zend_Controller_Request_Abstract $request)
    {
        if (!preg_match("/^klear/", $request->getModuleName()) || $request->getControllerName() == "assets") {
            return;
        }

        $this->_initPlugin();
        $this->_checkForbiddenExplorers();
    }

    protected function _initPlugin()
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam('bootstrap')->getResource('modules')->offsetGet('klear');
        $config = $bootstrap->getOption("klearBaseConfigFast");
        if (!isset($config->main)) {
           throw new Klear_Exception_MissingConfiguration('Main section is required on Browser Plugin');
        }
        $this->_mainConfig = $config->main;

    }

    protected function _checkForbiddenExplorers()
    {
        if ($this->_mainConfig->noIE) {
            $regex = "/(MSIE|Trident|Edge)/";
            if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
                echo "<center><font face='arial'>";
                echo "<h2>Lo sentimos, tu navegador no está soportado. / We are sorry. We don't support your browser.</h2>";
                echo "<p>".$_SERVER['HTTP_USER_AGENT']."</p>";
                echo $this->_getBrowsersLinks(array("chrome", "firefox", "opera"));
                echo "</font></center>";
                die();
            }
        }
    }

    protected function _getBrowsersLinks($browsers = array())
    {
        $availableBrowsers = array(
            "chrome" => '<a href="https://www.google.com/chrome/browser/desktop/index.html">'.
                            '<img height="100" src="https://www.google.com/intl/es_ALL/chrome/assets/common/images/chrome_logo_2x.png" alt="Chrome">'.
                        '</a>',
            "firefox" => '<a href="https://download.mozilla.org/">'.
                            '<img height="100" alt="Firefox para escritorio" src="http://mozorg.cdn.mozilla.net/media/img/firefox/new/header-firefox.png">'.
                        '</a>',
            "safari" => '<a href="https://support.apple.com/downloads/safari" sytle="text-decoration: none;">'.
                            '<img height="100" alt="Safari para escritorio" src="https://km.support.apple.com/kb/image.jsp?productid=PL165">'.
                            '<span style="font-size: 40px;font-style: normal;font-weight: normal;line-height: 30px;white-space: nowrap;width: 500px'.
                            'margin-bottom: 0;color: #333;">Safari</span>'.
                        '</a>',
            "opera" => '<a href="http://www.opera.com/">'.
                            '<img height="100" alt="opera" src="http://www-static.opera.com/static-heap/92/929b806843717c64f1e520052ad46620273d31c5/logo-header-opera.png">'.
                        '</a>',
        );
        $links = "";
        foreach ($browsers as $browser) {
            $links .= $availableBrowsers[$browser];
        }
        return $links;
    }
}
