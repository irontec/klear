<?php

/**
* Generador de identificativos únicos para identificativos únicos (rutas de fichero generalmente)
* Si la autenticación implementa Klear_Auth_Adapter_Interfaces_AdvancedUserModel,
* se añade "getUniqueIdenForCache" al id de cache
* @author jabi
*
*/
class Klear_Model_CacheKeyGenerator
{
    protected $_ignoreAuth = false;

    public function __construct(protected $_uniqueName)
    {}

    public function ignoreSessionAuth()
    {
        $this->_ignoreAuth = true;
    }

    public function getKey()
    {
        $baseKey = "congrio volador" . $this->_uniqueName;

        if (false === $this->_ignoreAuth &&
                \Zend_Auth::getInstance()) {

            $identity = \Zend_Auth::getInstance()->getIdentity();
            if ($identity) {
                if (is_subclass_of($identity, '\Klear_Auth_Adapter_Interfaces_AdvancedUserModel')) {
                    $baseKey .= $identity->getUniqueIdenForCache();
                }
            }
        }

        return md5($baseKey);
    }
}
