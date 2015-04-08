<?php
class Klear_Model_UserAdvanced extends Klear_Model_User implements Klear_Auth_Adapter_Interfaces_AdvancedUserModel
{
    public function getUniqueIdenForCache()
    {
        return $this->_id;
    }
}