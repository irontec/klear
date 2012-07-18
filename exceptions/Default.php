<?php
class Klear_Exception_Default extends \Zend_Exception
{
    public function __construct($msg = '', $code = 20000, Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }
}