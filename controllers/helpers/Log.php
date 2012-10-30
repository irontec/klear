<?php
/**
 * @author Alayn Gortazar <alayn+klear@irontec.com>
 *
 * Helper to provide an easy access to the Logger object
 *
 */
class Klear_Controller_Helper_Log extends Zend_Controller_Action_Helper_Abstract
{
    protected $_logger;

    public function __construct(Zend_Log $logger)
    {
        $this->_logger = $logger;
    }

    public function getLogger()
    {
        return $this->_logger;
    }

    public function log($message, $priority = Zend_Log::INFO, $extras = null)
    {
        return $this->_logger->log($message, $priority, $extras);
    }

    public function warn($message, $extras = null)
    {
        return $this->log($message, Zend_Log::WARN, $extras);
    }

    public function err($message, $extras = null)
    {
        return $this->log($message, Zend_Log::ERR, $extras);
    }

    public function direct($message, $priority = Zend_Log::INFO, $extras = null)
    {
        return $this->log($message, $priority, $extras);
    }
}
