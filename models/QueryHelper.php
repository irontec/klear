<?php
class Klear_Model_QueryHelper
{
    public static function replaceSelfReferences($haystack, $entityName, $appendStr = '')
    {
        $hasSelfReference = (strpos($haystack, 'self::') !== false);
        if ($hasSelfReference) {
            $replacement = empty($entityName) ? '' : $entityName . '.';
            return str_replace('self::', $replacement, $haystack) . $appendStr;
        }

        return $haystack;
    }

    public static function replaceSelfReferencesOrPrefix($haystack, $entityName, $appendStr = '')
    {
        $parsedHaystack = self::replaceSelfReferences($haystack, $entityName, $appendStr);

        if ($parsedHaystack !== $haystack) {
            return $parsedHaystack;
        }

        return $entityName . '.' . $haystack;
    }

}