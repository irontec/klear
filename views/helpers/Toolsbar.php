<?php

class Klear_View_Helper_Toolsbar extends Klear_View_Helper_Base
{
    public function setView(Zend_View_Interface $view)
    {
        $this->_view = $view;
    }

    public function Toolsbar()
    {
        $themeRoller = $this->_view->SiteConfig()->getThemeRoller($this->_view->baseUrl());
        $theme = $this->_view->SiteConfig()->getCurrentTheme();
        
        $ret = '<input type="checkbox" id="superCollapse" /> ' .
                '<label for="superCollapse" class="primary" title="'. $this->_view->translate("Collapse All.").'">' .
                '<span class="ui-icon ui-icon-triangle-1-nw" ></span></label>';
        
        $ret .= '<input type="checkbox" id="tabsPersist" />' .
                '<label for="tabsPersist" class="primary" title="' .$this->_view->translate("Remember opened main tabs.") . '">'.
                '<span class="ui-icon ui-icon-unlocked" ></span></label>';

        if ($langs = $this->_view->SiteConfig()->getLangs()) {
            if (sizeof($langs) > 1) {
                $ret .= '<input type="checkbox" id="langPicker" '.
                        '/><label for="langPicker" class="primary" title="' . $this->_view->translate("Change language") .'">' .
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
        if (count($themeRoller) > 0) {
            $ret .= '
                    <input type="checkbox" 
                        id="themeRoller" 
                        data-themes=\''.json_encode($themeRoller).'\' 
                        data-current="'.$theme.'"/> ' .
                        '<label for="themeRoller"  class="primary" 
                                title="'. $this->_view->translate("Change theme").'">' .
                        '<span class="ui-icon ui-icon-image" ></span>' .
                        '</label>
                            ';
            $ret .= '<select id="themeRollerSelector">';
            foreach ($themeRoller as $themeName=>$themePath) {
                $ret .= '<option';
                $ret .= ' value="' . $themePath . '" ';
                if ($themeName == $theme) {
                    $ret .= ' selected="selected" ';
                }
                $ret .= '>';
                $ret .= $themeName;
                $ret .= '</option>';
            }
            $ret .= '</select>';
        }
        
        $ret .= '<input type="checkbox" id="generalHelp" />' .
                '<label for="generalHelp" title="' .$this->_view->translate("Global help") . '" class="primary">'.
                '<span class="ui-icon ui-icon-info" ></span></label>';
        
        $ret .= '<input type="checkbox" id="logout" '.
                'data-url="' . $this->_view->url(array('controller' => 'index', 'action' => 'bye')).'"' .
                '/><label for="logout" class="primary" title="' . $this->_view->translate("logout") .'">' .
                '<span class="ui-icon ui-icon-power" ></span>' .
                '</label>'; 

        return $ret;

    }
}