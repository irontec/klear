<?php

class Klear_Model_User implements Klear_Auth_Adapter_Interfaces_BasicUserModel
{
    protected $_id;
    protected $_login;
    protected $_password;
    protected $_active;
    protected $_email;

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function setLogin($login)
    {
        $this->_login = $login;
        return $this;
    }

    public function setPassword($password)
    {
        $this->_password = $password;
        return $this;
    }

    public function setActive($active)
    {
        $this->_active = (bool) $active;
        return $this;
    }

    public function setEmail($email)
    {
        $this->_email = $email;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getLogin()
    {
        return $this->_login;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function isActive()
    {
        return (bool)$this->_active;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function __get($key)
    {
        if ($key === 'username') {
            return $this->getLogin();
        }
    }
}