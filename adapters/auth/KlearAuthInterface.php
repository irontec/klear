<?php

/**
 * Klear Authentication Adapters Should implement this interface
 */
interface Klear_Auth_Adapter_KlearAuthInterface extends Zend_Auth_Adapter_Interface
{
    public function __construct(Zend_Controller_Request_Abstract $request, Klear_Model_ConfigParser $authConfig = null);

    /**
     * Write identity data on Zend_Auth storage.
     */
    public function saveStorage();
}

//EOF