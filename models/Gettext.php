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

        $validFunctions = array("ngettext","_");

        $string = trim($string);

        //Detectamos que el literal no esté contenido en una de las funciones gettext

        $quotedValidFunctions = array();
        foreach ($validFunctions as $funcName) {
            $quotedValidFunctions[] = '' . preg_quote($funcName . '(', "/") . '';
        }

        if (!preg_match("/^".implode("|", $quotedValidFunctions)."/i", $string, $result)) {

            return $string;
        }

        $tokens = token_get_all("<?php ".$string." ?>");

        $literal = '';
        $opened = 0;
        $curFunction = false;
        $arguments = array();

        $totalTokens = sizeof($tokens);

        foreach ($tokens as $idToken => $token) {
            if (is_string($token)) {
                switch($token) {
                    case ')':
                        $opened -=1;
                        if ($opened == 0) {
                            break 2;
                        }

                        break;

                    case '(':
                        $opened +=1;
                        break;

                }
                continue;
            }

            switch(token_name($token[0])) {
                case 'T_STRING':

                    // Nombre de función
                    if (in_array($token[1], $validFunctions)) {

                        if ($curFunction === false) {
                            $curFunction = $token[1];
                            $arguments = array();
                        } else {

                            $newString = '';

                            for ($j = $idToken;$j<$totalTokens;$j++) {
                                if (is_string($tokens[$j])) {

                                    $newString .= $tokens[$j];
                                } else {
                                    $newString .= $tokens[$j][1];
                                }
                            }

                            $arguments[] = self::gettextCheck($newString);
                        }

                    } else {
                        var_dump($string, $token);echo "WTF";exit;

                        Throw new Exception("Invalid gettext string");

                    }

                    break;
                case 'T_CONSTANT_ENCAPSED_STRING':
                case 'T_LNUMBER':
                case 'T_DNUMBER':
                    // Literal string (con comillas o espacios en blanco)
                    // Literal número
                    if (($curFunction === false) || ($opened == 0)) {
                        Throw new Exception("Invalid gettext string");
                    }
                    if ($opened == 0) {
                        $literal .= $token[1];
                    }
                    if ($opened == 1) {
                        $arguments[] = preg_replace("|^[\'\"](.*)[\'\"]$|", "$1", $token[1]);
                    }

                    break;
                case 'T_WHITESPACE':
                    $literal .= $token[1];
                    break;

            }

        }

        $translator = Zend_Registry::get(Klear_Plugin_Translator::DEFAULT_REGISTRY_KEY);

        switch($curFunction) {
            case "_":

                $arguments[0] = $translator->translate($arguments[0]);

                if (sizeof($arguments) > 1) {
                    return call_user_func_array("sprintf", $arguments);
                } else {
                    return $arguments[0];
                }
                break;

            case "ngettext":

                return call_user_func_array(array($translator, 'plural'), $arguments);
                break;
        }

        Throw Exception("Invalid function in gettext");


    }
}
