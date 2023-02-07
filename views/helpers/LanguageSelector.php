<?php

class Klear_View_Helper_LanguageSelector extends Klear_View_Helper_Base
{
    public function setView(Zend_View_Interface $view)
    {
        $this->_view = $view;
    }

    public function LanguageSelector()
    {
        $ret = '';

        if ($langs = $this->_view->SiteConfig()->getLangs()) {

            if ((is_countable($langs) ? count($langs) : 0) > 1) {
                $ret .= '<div id="loginTools">
                <div id="loginToolsbar">';

                $ret .= '<input type="checkbox" id="langPickerLogin" '.
                        '/><label for="langPickerLogin" title="' .
                        $this->_view->translate("Change language") .
                        '" class="ui-corner-right">' .
                        '<span class="ui-icon ui-icon-flag " ></span>' .
                        '</label>';

                foreach ($langs as $_langIden => $lang) {

                    $ret .= '<input type="radio" name="lang" class="pickableLang" value="' . $_langIden . '" ' .
                            ' id="lang' . $_langIden . 'Login" ';

                    if ($_langIden == $this->_view->SiteConfig()->getLang()->getIden()) {
                        $ret .= ' checked="checked"';
                    }

                    $ret .= '/><label for="lang' . $_langIden . 'Login"
                            title="' . $lang . '" class="expanded pickableLanguage">' .
                            $lang .
                            '</label>';
                }

                $ret .= '</div></div>';
            }
        }

        return $ret;
    }
}
