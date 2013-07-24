<?php

class Klear_Auth_Adapter_NoAuth implements \Klear_Auth_Adapter_KlearAuthInterface
{
    protected $_username;
    protected $_password;
    protected $_userId;

    protected $_userMapper;

    /**
     *
     * @var Klear_Auth_Adapter_Interfaces_BasicUserModel
     */
    protected $_user;

    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Klear_Model_ConfigParser $authConfig
     *
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * $authConfig no se usa porque no se necesita,
     * pero es obligatorio para cumplir con la interfaz
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Klear_Model_ConfigParser $authConfig = null)
    {
        $this->_username = $request->getPost('username', '');
    }

    public function authenticate()
    {
        $this->_user = new \Klear_Model_User();
        $this->_user->setLogin($this->_username);

        $authResult = Zend_Auth_Result::SUCCESS;
        $authMessage = array("message"=>"Welcome!");
        return new Zend_Auth_Result($authResult, $this->_username, $authMessage);
    }

    public function saveStorage()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $authStorage->write($this->_user);
    }
}
