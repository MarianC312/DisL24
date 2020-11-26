<?php
    class Sistema{
        public static $charReplace = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        private static $alg = "sha512";
        private static $key = "m\$t*rK.yEf3c";

        public static $version = "alpha-1.6.14g";

        public static function textoSinAcentos($string){
            return strtr( $string, Sistema::$charReplace );
        }

        public static function textoSinSignos($texto){
            return preg_replace('/[^a-zA-Z0-9 ]/','', $texto);
        }

        public static function dbGetCantidadProductoPorPrefijo($prefijo){
            if(Sistema::usuarioLogueado()){
                $buscar = false;
                switch(strlen($prefijo)){
                    case 3:
                        $buscar = true;
                    break;
                    case 5:
                        $buscar = true;
                    break;
                    case 8:
                        $buscar = true;
                    break;
                }
                if($buscar){
                    $query = DataBase::select("producto", "SUBSTRING(codigoBarra, 1, ".strlen($prefijo).") AS 'prefijo', COUNT(SUBSTRING(codigoBarra, 1 ,".strlen($prefijo).")) AS 'cantidad'", "codigoBarra LIKE '".$prefijo."%'", "GROUP BY SUBSTRING(codigoBarra, 1, ".strlen($prefijo).")");
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
                        Sistema::debug('error', 'sistema.class.php - dbGetCantidadProductoPorPrefijo - Error en consulta. Ref.: '.DataBase::getError());
                    }
                }
            }else{
                Sistema::debug('error', 'sistema.class.php - dbGetCantidadProductoPorPrefijo - Usuario no logueado.');
            }
            return false;
        }

        public static function dbGetTamañoCodigoBarra(){ //cantidad de productos por cantidad de char en codigo de barra
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                if($_SESSION["usuario"]->isAdmin()){
                    $query = DataBase::select("producto", "CHAR_LENGTH(codigoBarra) AS 'Tamaño código', COUNT(CHAR_LENGTH(codigoBarra)) AS 'cantidad'", "1", "GROUP BY CHAR_LENGTH(codigoBarra)");
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
                        Sistema::debug('error', 'sistema.class.php - dbGetTamañoCodigoBarra - Error en consulta. Ref.: '.DataBase::getError());
                    }
                }
            }else{
                Sistema::debug('error', 'sistema.class.php - dbGetTamañoCodigoBarra - Usuario no logueado.');
            }
            return false;
        }

        public static function getDigitoControl($codigo){
            $digitos = strlen($codigo);
            switch($digitos){
                case 11:

                break;
                case 12:

                break;
                case 14:

                break;
            }
        }

        public static function in_array_r($needle, $haystack, $strict = false) {
            foreach ($haystack as $item) {
                if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && Sistema::in_array_r($needle, $item, $strict))) {
                    return true;
                }
            }
            return false;
        }

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

        public static function compañiaSucursalCajaHistorialUpdate($idHistorial){
            if(Sistema::usuarioLogueado()){
                if(isset($idHistorial) && is_numeric($idHistorial) && $idHistorial > 0){
                    $query = DataBase::update("compañia_sucursal_caja_historial", "procesado = 1", "id = '".$idHistorial."' AND procesado = 0 AND fechaModificacion IS NULL");
                    if($query){
                        return true;
                    }else{
                        Sistema::debug('error', 'sistema.class.php - compañiaSucursalHistorialCajaUpdate - Error al realizar el update del historial. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'sistema.class.php - compañiaSucursalHistorialCajaUpdate - Hubo un error en el identificador recibido. Ref.: '.$idHistorial);
                }
            }else{
                Sistema::debug('error', 'sistema.class.php - compañiaSucursalHistorialCajaUpdate - Usuario no logueado.');
            }
            return false;
        }

        public static function compañiaSucursalCajaUpdate($data, $try = 0){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    $update = Caja::update($data["monto"], $data["identificador"], $data["operador"], $data["sucursal"], $data["compañia"]);
                    if($update){
                        if(!Sistema::compañiaSucursalCajaHistorialUpdate($data["identificador"])){
                            $mensaje['tipo'] = 'info';
                            $mensaje['cuerpo'] = 'Hubo un error al actualizar el estado del movimiento. <b>Informe al administrador a la brevedad</b>';
                            Alert::mensaje($mensaje);
                        }
                        return true;
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al actualizar el monto de la caja. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                        Sistema::debug('error', 'sistema.class.php - compañiaSucursalCajaUpdate - Hubo un error al actualizar la caja. Ref.: false');
                    }
                }else{
                    $mensaje['tipo'] = 'alert';
                    $mensaje['cuerpo'] = 'Hubo un error al actualizar la caja de la sucursal. <b>Informe al administrador a la brevedad.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('warning', 'sistema.class.php - compañiaSucursalCajaUpdate - Error en el arreglo de datos recibido.');
                }
            }else{
                Sistema::debug('error', 'sistema.class.php - compañiaSucursalCajaUpdate - Usuario no logueado.');
            }
            return false;
        }

        public static function reloadStaticData(){
            Sistema::debug('info', 'sistema.class.php - reloadStaticData - Inicio recarga de datos estáticos...');
            Session::iniciar();
            $_SESSION["usuario"]->reloadStaticData();
            $_SESSION["lista"]["producto"] = Lista::producto();
            $_SESSION["lista"]["producto"]["tipo"] = Lista::productoTipo();
            $_SESSION["lista"]["producto"]["categoria"] = Lista::productoCategoria();
            $_SESSION["lista"]["producto"]["subcategoria"] = Lista::productoSubCategoria();
            $_SESSION["lista"]["fabricante"] = Lista::fabricante();
            $_SESSION["lista"]["proveedor"] = Lista::proveedor();
            $_SESSION["lista"]["sucursal"] = Lista::sucursal();
            $_SESSION["lista"]["compañia"] = Lista::compañia();
            $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["data"] = Compania::data();
            $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["cliente"] = Lista::compañiaCliente();
            $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"] = Compania::stockData();
            $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["facturacion"] = Compania::facturacionData($_SESSION["usuario"]->getCompañia());
            $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["facturacion"]["pendiente"] = Compania::facturacionData($_SESSION["usuario"]->getCompañia(), 1);
            $_SESSION["lista"]["caja"]["accion"]["tipo"] = Lista::cajaAccionTipo();
            $_SESSION["lista"]["pago"] = Lista::pago();
            $_SESSION["componente"]["header"]["usuario"]["data"] = Sistema::componenteEstado(2);
            $_SESSION["componente"]["header"]["usuario"]["opcion"] = (isset($_SESSION["componente"]["header"]["usuario"]["opcion"])) ? $_SESSION["componente"]["header"]["usuario"]["opcion"] : [];
            $_SESSION["componente"]["menu"]["data"] = Sistema::componenteEstado(1);
            $_SESSION["componente"]["menu"]["data"]["opcion"] = (isset($_SESSION["componente"]["menu"]["data"]["opcion"])) ? $_SESSION["componente"]["menu"]["data"]["opcion"] : [];
            Sistema::debug('info', 'sistema.class.php - reloadStaticData - Fin recarga de datos estáticos.');
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

        public static function getFileData($tipo, $url){
            if(Sistema::usuarioLogueado()){
                switch($tipo){
                    case 'csv':
                        return Sistema::fileCSV($url);
                    break;
                    case 'json':
                        return Sistema::fileJSON($url);
                    break;
                }
            }else{
                Sistema::debug('error', 'sistema.class.php - openFile - Usuario no logueado.');
            }
        }

        private static function fileCSV($url){
            if(Sistema::usuarioLogueado()){
                $data = [];
                if (($h = fopen($url, "r")) !== FALSE){
                    while (($fileData = fgetcsv($h, 1000, ",")) !== FALSE){	
                        $data[] = $fileData;
                    }
                    fclose($h);
                }
                return $data;
            }else{
                Sistema::debug('error', 'sistema.class.php - fileCSV - Usuario no logueado.');
            }
        }

        private static function fileJSON($url){
            if(Sistema::usuarioLogueado()){
                $str = file_get_contents($url);
                $data = json_decode($str, true);
                return $data;
            }else{
                Sistema::debug('error', 'sistema.class.php - fileJSON - Usuario no logueado.');
            }
        }

        private static function accionesConDatos(){
            if (($h = fopen('./data/pcuidados/productos.csv', "r")) !== FALSE){
                while (($data = fgetcsv($h, 1000, ",")) !== FALSE){	
                    echo '<pre>';
                    print_r($data);
                    echo '</pre>';
                }
                // Close the file
                fclose($h);
            }

            if(1 == 2){
                $_SESSION["producto"] = [];
                $dif = [0,4,6,20,30,15];
                $exc = [0,8,17,21,33,40];
                if (($h = fopen('./data/productos2.csv', "r")) !== FALSE){
                    while (($data = fgetcsv($h, 1000, ",")) !== FALSE){	
                        $cat = array_keys($_SESSION["lista"]["producto"]["categoria"], $data[2]);
                        if(count($cat) > 0){
                            $categoria = $cat[0];
                        }else{
                            $aFlag = array_keys($_SESSION["categoria"], $data[2]);
                            $flag = array_keys($exc, $aFlag[0]);
                            $categoria = $dif[$flag[0]];
                        } 
                        if($data[1] != "Descripcion"){ 
                            
                        }
                    }
                    // Close the file
                    fclose($h);
                }
                echo '<pre>';
                print_r($_SESSION["producto"]);
                echo '</pre>';
                exit;
                
                $start = 45449;
                foreach($_SESSION["producto"] AS $key => $value){
                    if($key >= $start){
                        $query = DataBase::insert("producto", "nombre,tipo,codigoBarra,categoria", "'".$value["nombre"]."','".$value["tipo"]."','".$value["codigo"]."','".$value["categoria"]."'");
                        if($query){
                            echo DataBase::getLastId()." | ".$value["nombre"]." OK. <br>";
                        }else{
                            echo $value["nombre"]." ERROR.<br> ".DataBase::getError()." <br><br>";
                        }
                    }
                }
            }

            if(1 == 2){
                $str = file_get_contents('./data/pmaximos/tucuman.json');
                $data = json_decode($str, true);
                foreach($data AS $key => $value){
                    foreach($value AS $iKey => $iValue){
                        if(Sistema::in_array_r($iValue["id_producto"], $_SESSION["lista"]["producto"])){
                            $query = DataBase::update("producto", "subcategoria = 1", "codigoBarra = '".$iValue["id_producto"]."'");
                            if($query){
                                echo $iValue["Producto"]." registro modificado.<br>";
                            }else{
                                echo $iValue["Producto"]." ERROR.<br> ".DataBase::getError()." <br><br>";
                            }
                        }
                    }
                }
            }
        }
    }
?>