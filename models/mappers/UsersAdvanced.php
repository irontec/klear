<?php
class Klear_Model_Mapper_UsersAdvanced extends Klear_Model_Mapper_Users  
{
    public function findByLogin($login) 
    {
        $select = $this->_dbTable->select()->where('login = ?', $login);
        $row = $this->_dbTable->fetchRow($select);
        if ($row) {
            $user = new Klear_Model_UserAdvanced();
            return $this->_poblateUser($user, $row);
        }
        return null;
    }
}
