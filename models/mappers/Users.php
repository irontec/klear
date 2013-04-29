<?php
class Klear_Model_Mapper_Users implements Klear_Auth_Adapter_Interfaces_BasicUserMapper
{
    protected $_dbTable;

    protected $_timezoneKey = false;
    protected $_timezoneGetter;
    protected $_timezoneMapper;
    public function __construct()
    {
        $this->_dbTable = new Klear_Model_DbTable_Users();
    }
    
    public function setTimezoneKey($key)
    {
        $this->_timezoneKey = $key;
    }
    
    public function setTimezoneMapper($mapper)
    {
        $this->_timezoneMapper = $mapper;
    }
    
    public function setTimezoneGetter($getter)
    {
        $this->_timezoneGetter = $getter;
    }
    
    public function findByLogin($login) 
    {
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
        if ($this->_timezoneKey !== false) {
            
            $tzKey = $this->_timezoneKey;
            $mapper = $this->_timezoneMapper;
            $getter = $this->_timezoneGetter;
            
            $tzModel = $mapper->find($row->{$tzKey});
            if (is_object($tzModel)) {
                $user->setTimezone($tzModel->{$getter}());
            }
            
        } 
        return $user;
    }
}
