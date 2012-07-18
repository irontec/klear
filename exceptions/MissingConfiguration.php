<?php
class Klear_Exception_MissingConfiguration extends \Klear_Exception_Default
{
    public function __construct($msg = 'Missed configuration param', $code = 20000, Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }
}