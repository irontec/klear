<?php

class Klear_Model_YamlExpression
{
    /**
     * Check a given YAML expression only has valid values
     *
     * @param unknown_type $string
     */
    public static function checkTokens($string)
    {
        $validStringTokens = array("true", "false", ";", ">", "<", "(", ")");
        $string = trim($string);
        $tokens = \PhpToken::tokenize("<?php " . $string . " ?>");

        foreach ($tokens as $idToken => $token) {

            if (is_string($token)) {
                // Check if the string is one of the supported strings
                if (!in_array($token, $validStringTokens)) {
                    throw new Exception("Invalid token string in YAML expression:" . $string . ' ' . $token);
                }
                continue;
            }

            switch($token->getTokenName()) {
                case 'T_STRING':
                    // Check if the string is one of the supported strings
                    if (!in_array($token->text, $validStringTokens)) {
                        throw new Exception("Invalid token string in YAML expression:" . $string . ' ' . $token);
                    }
                    break;
                case 'T_OPEN_TAG':
                case 'T_CLOSE_TAG':
                case 'T_RETURN':
                case 'T_LNUMBER':
                case 'T_WHITESPACE':
                case 'T_IS_EQUAL':              // ==
                case 'T_IS_GREATER_OR_EQUAL':   // >=
                case 'T_IS_IDENTICAL':          // ===
                case 'T_IS_NOT_EQUAL':          // != or <>
                case 'T_IS_NOT_IDENTICAL':      // ===
                case 'T_IS_SMALLER_OR_EQUAL':   // <=
                case 'T_LOGICAL_AND':           // and
                case 'T_LOGICAL_OR':            // or
                case 'T_BOOLEAN_AND':           // &&
                case 'T_BOOLEAN_OR':            // ||
                    break;
                default:
                    throw new Exception("Invalid token in YAML expression");
                    break;
            }
        }

        return $string;
    }
}
