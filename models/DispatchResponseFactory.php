<?php
class Klear_Model_DispatchResponseFactory
{
    public static function build()
    {
//         Zend_Json::$useBuiltinEncoderDecoder = true;
        $jsonResponse = new Klear_Model_DispatchResponse();
        $jsonResponse->setModule('klear');
        $jsonResponse->setPlugin(false);

        return $jsonResponse;
    }
}