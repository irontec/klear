<?php
class Klear_Model_Mapper_Users implements Klear_Auth_Adapter_Interfaces_BasicUserMapper
{
    protected $_dbTable;

    public function __construct()
    {
        $this->_dbTable = new Klear_Model_DbTable_Users();
    }

    public function findByLogin($login) {
        $select = $this->_dbTable->select()->where('login = ?', $login);
        $row = $this->_dbTable->fetchRow($select);
        if ($row) {
            $user = new Klear_Model_User();
            return $this->_poblateUser($user, $row);
        }
        return null;
    }

    protected function _poblateUser(Klear_Model_User $user, Zend_Db_Table_Row $row)
    {
        $user->setId($row->userId);
        $user->setLogin($row->login);
        $user->setEmail($row->email);
        $user->setPassword($row->pass);
        $user->setActive($row->active);
        return $user;
    }
}