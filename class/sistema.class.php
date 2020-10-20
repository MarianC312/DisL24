<?php
    class Sistema{
        private static $alg = "sha512";
        private static $key = "m\$t*rK.yEf3c";

        public static function loading($tipo = "loader"){
            return ('<span class="'.$tipo.'"></span>');
        }

        public static function usuarioLogueado(){
            Session::iniciar();
            return (isset($_SESSION["usuario"]) && $_SESSION["usuario"]->getAuth()) ? true : false;
        }

        public static function hashGet($data){
            return Sistema::hashCreate($data);
        }

        public static function alertGetData($tipo = null){
            $query = DataBase::select("sistema_alerta", "*", ((is_numeric($tipo)) ? "tipo = '".$tipo."'" : "1")." AND (destinatario = '".$_SESSION["usuario"]->getId()."' OR destSucursal = '".$_SESSION["usuario"]->getSucursal()."')","");
            if($query){
                $data = [];
                if(DataBase::getNumRows($query) > 0){
                    while($dataQuery = DataBase::getArray($query)){
                        $data[] = $dataQuery;
                    }
                    foreach($data AS $key => $value){
                        foreach($value AS $iKey => $iValue){
                            if(is_int($iKey)){
                                unset($data[$key][$iKey]);
                            }
                        }
                    } 
                }
                return $data;
            }else{
                Sistema::debug("error", "sistema.class.php - alertGetData - Error en query.");
                return false;
            }
        }

        public static function debug($tipo, $text){
            Session::iniciar();
            if(!is_null($_SESSION["usuario"]->debug()) && $_SESSION["usuario"]->debug() != "no"){
                if($_SESSION["usuario"]->debug() === "all"){
                    echo '<script>console.log("'.mb_strtoupper($tipo).': \n'.$text.'")</script>';
                    return;
                }
                switch($tipo){
                    case "error":
                        echo ($_SESSION["usuario"]->debug() == "error") ? '<script>console.log(Error: \n"'.$text.'")</script>' : '<script>console.log("Debug de errores inhabilitado.")</script>';
                    break;
                    case "info":
                        echo ($_SESSION["usuario"]->debug() == "info") ? '<script>console.log(Info: \n"'.$text.'")</script>' : '<script>console.log("Debug de información inhabilitado.")</script>';
                    break;
                    case "alert":
                        echo ($_SESSION["usuario"]->debug() == "alert") ? '<script>console.log(Alert: \n"'.$text.'")</script>' : '<script>console.log("Debug de alertas inhabilitado.")</script>';
                    break;
                    case "success":
                        echo ($_SESSION["usuario"]->debug() == "success") ? '<script>console.log(Success: \n"'.$text.'")</script>' : '<script>console.log("Debug de succeded inhabilitado.")</script>';
                    break;
                    case "all":
                        echo '<script>console.log('.mb_strtoupper($tipo).': \n"'.$text.'")</script>';
                    break;
                }
            }
        }

        public static function reloadStaticData(){
            Session::iniciar();
            $_SESSION["usuario"]->reloadStaticData();
            $_SESSION["lista"]["producto"]["tipo"] = Lista::productoTipo();
            $_SESSION["lista"]["producto"]["categoria"] = Lista::productoCategoria();
            $_SESSION["lista"]["producto"]["subcategoria"] = Lista::productoSubCategoria();
            $_SESSION["lista"]["fabricante"] = Lista::fabricante();
            $_SESSION["lista"]["proveedor"] = Lista::proveedor();
            $_SESSION["lista"]["sucursal"] = Lista::sucursal();
            $_SESSION["lista"]["compañia"] = Lista::compañia();
            $_SESSION["componente"]["header"]["usuario"]["data"] = Sistema::componenteEstado(2);
            $_SESSION["componente"]["header"]["usuario"]["opcion"] = (isset($_SESSION["componente"]["header"]["usuario"]["opcion"])) ? $_SESSION["componente"]["header"]["usuario"]["opcion"] : [];
            $_SESSION["componente"]["menu"]["data"] = Sistema::componenteEstado(1);
            $_SESSION["componente"]["menu"]["data"]["opcion"] = (isset($_SESSION["componente"]["menu"]["data"]["opcion"])) ? $_SESSION["componente"]["menu"]["data"]["opcion"] : [];
        }

        public static function componenteEstado($idComponente){
            Session::iniciar();
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
                        return $data;
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