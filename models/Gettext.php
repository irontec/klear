<?php
class Klear_Model_Gettext
{
    /**
     * Miramos "a lo gettext para traducirlo automáticamente"
     *
     * @param unknown_type $string
     */
    public static function gettextCheck($string)
    {
        $pregMixed = preg_match_all(
                    "|_\([\'\"](.*)[\'\"],[\s.]_\([\'\"](.*)[\'\"]\)|U",
                    $string,
                    $resultMixed,
                    PREG_PATTERN_ORDER);
        $preg = preg_match_all(
                "|_\([\'\"](.*)[\'\"]\)|U",
                $string,
                $result,
                PREG_PATTERN_ORDER);

        $pregPlural = preg_match_all(
                "|ngettext\([\'\"](.*)[\'\"],[\s][\'\"](.*)[\'\"],[\s](.*)\)|U",
                $string,
                $resultPlural,
                PREG_PATTERN_ORDER);

        $translator = Zend_Registry::get(Klear_Plugin_Translator::DEFAULT_REGISTRY_KEY);
        if ($pregMixed == 1 && count($resultMixed) == 3) {
            $replace = array();
            foreach ($resultMixed[2] as $match) {
                $item = $translator->{'translate'}($match);
            }
            foreach ($resultMixed[1] as $match) {
                $string = sprintf($translator->{'translate'}($match), $item);
            }
        } elseif ($preg == 1 && is_array($result)) {
            $replace = array();
            foreach ($result[1] as $match) {
                $replace[] = $translator->{'translate'}($match);
            }
            $string = str_replace($result[0], $replace, $string);
        } elseif ($pregPlural == 1 && count($resultPlural) == 4 ) {
            $item = $translator->{'plural'}($resultPlural[1][0], $resultPlural[2][0], $resultPlural[3][0]);
            $string = str_replace($resultPlural[0][0], $item, $string);
        }
        return $string;
    }
}