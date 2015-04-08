<?php
interface Klear_Auth_Adapter_Interfaces_AdvancedUserModel extends Klear_Auth_Adapter_Interfaces_BasicUserModel
{
    /**
     * Get auth based unique identifier for caching purpouses
     */
    public function getUniqueIdenForCache();
}