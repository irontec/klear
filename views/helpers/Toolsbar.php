<?php

class Klear_View_Helper_Toolsbar extends Klear_View_Helper_Base
{
    public function setView(Zend_View_Interface $view)
    {
        $this->_view = $view;
    }

    public function Toolsbar()
    {
        $ret = '<input type="checkbox" id="superCollapse" /> ' .
                '<label for="superCollapse" title="'. $this->_view->translate("Collapse All.").'">' .
                '<span class="ui-icon ui-icon-triangle-1-nw" ></span>' .
                '</label>';

//     	$ret .= '<input type="checkbox" id="menuCollapse" /> ' .
//             '<label for="menuCollapse" title="'. $this->_view->translate("Collapse Menu.").'">' .
//             '<span class="ui-icon ui-icon-triangle-1-w" ></span>' .
//             '</label>';

//         $ret .= '<input type="checkbox" id="headerCollapse" /> ' .
//         		'<label for="headerCollapse" title="'. $this->_view->translate("Collapse Header.").'">' .
//         		'<span class="ui-icon ui-icon-triangle-1-n" ></span>' .
//         		'</label>';

        $ret .= '<input type="checkbox" id="tabsPersist" />' .
            '<label for="tabsPersist" title="' .$this->_view->translate("Remember opened main tabs.") . '">'.
            '<span class="ui-icon ui-icon-unlocked" ></span>' .
            '</label>';

        if ($langs = $this->_view->SiteConfig()->getLangs()) {
            if (sizeof($langs) > 1) {
                $ret .= '<input type="checkbox" id="langPicker" '.
                        '/><label for="langPicker" title="' . $this->_view->translate("Change language") .'">' .
                        '<span class="ui-icon ui-icon-flag" ></span>' .
                        '</label>';

                foreach ($langs as $_langIden => $lang) {
                    $ret .= '<input type="radio" name="lang" class="pickableLang" value="'.$_langIden.'" ' .
                            ' id="lang' .$_langIden.'" ';
                    if ($_langIden == $this->_view->SiteConfig()->getLang()->getIden()) {
                        $ret .= ' checked="checked"'; 
                    }

                    $ret .= '/><label for="lang'.$_langIden.'" title="' . $lang.'" class="expanded pickableLanguage">' .
                            $lang .
                            '</label>';
                } 
            }
        }
        
        
        $ret .= '<input type="checkbox" id="logout" '.
                'data-url="' . $this->_view->url(array('controller' => 'index', 'action' => 'bye')).'"' .
                '/><label for="logout" title="' . $this->_view->translate("logout") .'">' .
                '<span class="ui-icon ui-icon-power" ></span>' .
                '</label>'; 

        return $ret;

    }
}