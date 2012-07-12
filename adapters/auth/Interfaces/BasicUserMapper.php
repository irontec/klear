<?php
interface Klear_Auth_Adapter_Interfaces_BasicUserMapper
{
    /**
     * @param string $login
     * @return Klear_Auth_Adapter_Interfaces_BasicUserModel
     */
    public function findByLogin($login);
}