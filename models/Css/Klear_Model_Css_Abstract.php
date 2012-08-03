<?php
class Klear_Model_Css_Abstract
{
    abstract public function __construct(Zend_Config $config);
    abstract public function getCssFile($filename);
    abstract public function getPngFile($filename);
}
