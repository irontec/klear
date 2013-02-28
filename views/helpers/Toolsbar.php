<?php

class Klear_View_Helper_Toolsbar extends Klear_View_Helper_Base
{
    public function setView(Zend_View_Interface $view)
    {
        $this->_view = $view;
    }

    public function Toolsbar()
    {
        return
            '<input type="checkbox" id="menuCollapse" /> ' .
            '<label for="menuCollapse" title="'. $this->_view->translate("Collapse Menu.").'">' .
            '<span class="ui-icon ui-icon-arrowthickstop-1-w" ></span>' .
            '</label>'.

            '<input type="checkbox" id="tabsPersist" />' .
            '<label for="tabsPersist" title="' .$this->_view->translate("Remember opened main tabs.") . '">'.
            '<span class="ui-icon ui-icon-unlocked" ></span>' .
            '</label>' .

            '<input type="checkbox" id="logout" '.
            'data-url="' . $this->_view->url(array('controller' => 'index', 'action' => 'bye')).'"' .
            '/><label for="logout" title="' . $this->_view->translate("logout") .'">' .
            '<span class="ui-icon ui-icon-power" ></span>' .
            '</label>';
    }
}