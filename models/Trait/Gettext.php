<?php
trait Klear_Model_Trait_Gettext 
{
    /**
     * Miramos "a lo gettext para traducirlo automáticamente"
     * 
     * @param unknown_type $string
     */
    protected function _gettextCheck($string)
    {
        $preg = preg_match_all(
                "|_\([\'\"](.*)[\'\"]\)|U",
                $string,
                $result,
                PREG_PATTERN_ORDER);
        if ($preg && is_array($result)) {
            $replace = array();
            $translator = Zend_Registry::get(Klear_Plugin_Translator::DEFAULT_REGISTRY_KEY);
            foreach ($result[1] as $match) {
                $replace[] = $translator->{'translate'}($match);
            }
            $string = str_replace($result[0], $replace, $string);
        }
        return $string;
    }
}