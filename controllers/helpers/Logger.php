<?php
class Klear_Action_Helper_Logger extends Zend_Controller_Action_Helper_Abstract
{
    protected $_logger;

    public function __construct(Zend_Log $logger)
    {
        $this->_logger = $logger;
    }

    public function logger()
    {
        return $this->_logger;
    }

    public function direct()
    {
        return $this->logger();
    }
}