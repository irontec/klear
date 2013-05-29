<?php
interface Klear_Auth_Adapter_Interfaces_BasicUserModel
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getLogin();

    /**
     * @return string password hash
     */
    public function getPassword();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return bool
     */
    public function getAdministrator();
}