<?php
    class Sistema{
        private static $alg = "sha512";
        private static $key = "m\$t*rK.yEf3c";

        public static function hashGet($data){
            return Sistema::hashCreate($data);
        }

        public static function componenteEstado($idComponente){
            if(isset($idComponente) && is_numeric($idComponente) && $idComponente > 0){
                $query = DataBase::select("sistema_componente", "*", "id = '".$idComponente."'", "");
                if($query){
                    if(DataBase::getNumRows($query) == 1){
                        $data = DataBase::getArray($query);
                        foreach($data AS $key => $value){
                            if(is_int($key)){
                                unset($data[$key]);
                            }
                        }
                        return Sistema::json_format($data);
                    }else{
                        return 0;
                    }
                }else{
                    return false;
                }
            }else{
                return null;
            }
        }

        public static function json_format($json) {
            if (!is_string($json)) {
                if (phpversion() && phpversion() >= 5.4) {
                    return json_encode($json, JSON_PRETTY_PRINT);
                }
                $json = json_encode($json);
            }
            $result      = '';
            $pos         = 0;               // indentation level
            $strLen      = strlen($json);
            $indentStr   = "\t";
            $newLine     = "\n";
            $prevChar    = '';
            $outOfQuotes = true;
          
            for ($i = 0; $i < $strLen; $i++) {
            // Speedup: copy blocks of input which don't matter re string detection and formatting.
            $copyLen = strcspn($json, $outOfQuotes ? " \t\r\n\",:[{}]" : "\\\"", $i);
            if ($copyLen >= 1) {
                $copyStr = substr($json, $i, $copyLen);
                // Also reset the tracker for escapes: we won't be hitting any right now
                // and the next round is the first time an 'escape' character can be seen again at the input.
                $prevChar = '';
                $result .= $copyStr;
                $i += $copyLen - 1;      // correct for the for(;;) loop
                continue;
            }
            
            // Grab the next character in the string
            $char = substr($json, $i, 1);
            
            // Are we inside a quoted string encountering an escape sequence?
            if (!$outOfQuotes && $prevChar === '\\') {
                // Add the escaped character to the result string and ignore it for the string enter/exit detection:
                $result .= $char;
                $prevChar = '';
                continue;
            }
            // Are we entering/exiting a quoted string?
            if ($char === '"' && $prevChar !== '\\') {
                $outOfQuotes = !$outOfQuotes;
            }
            // If this character is the end of an element,
            // output a new line and indent the next line
            else if ($outOfQuotes && ($char === '}' || $char === ']')) {
                $result .= $newLine;
                $pos--;
                for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
                }
            }
            // eat all non-essential whitespace in the input as we do our own here and it would only mess up our process
            else if ($outOfQuotes && false !== strpos(" \t\r\n", $char)) {
                continue;
            }
        
            // Add the character to the result string
            $result .= $char;
            // always add a space after a field colon:
            if ($outOfQuotes && $char === ':') {
                $result .= ' ';
            }
        
            // If the last character was the beginning of an element,
            // output a new line and indent the next line
            else if ($outOfQuotes && ($char === ',' || $char === '{' || $char === '[')) {
                $result .= $newLine;
                if ($char === '{' || $char === '[') {
                $pos++;
                }
                for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
                }
            }
            $prevChar = $char;
            }
        
            return $result;
        }

        private static function hashCreate($data){
            $context = hash_init(Sistema::$alg, HASH_HMAC, Sistema::$key);
            hash_update($context, $data); 
            return hash_final($context);
        }
    }
?>