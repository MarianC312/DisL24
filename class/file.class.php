<?php
    class File
    {
        public static function urlHost()
        {
            $urlHost = ($_SERVER['HTTP_HOST'] == "localhost") ? "http://".$_SERVER['HTTP_HOST']."/DisL24/" : "https://".$_SERVER['HTTP_HOST']."/mine/";
            return $urlHost;
        }

        public static function absolutePath()
        {
            $root = ($_SERVER['HTTP_HOST'] == "localhost") ? $_SERVER['DOCUMENT_ROOT']."/DisL24/" : $_SERVER['DOCUMENT_ROOT']."/mine/";
            return $root;
        }

        public static function uploadToLibrary($data){
            if(isset($data) && is_array($data) && count($data["file"]) > 0){
                $oficina = Lista::oficina();
                $response = [];
                foreach($data["file"] AS $key => $value){
                    $name = str_replace(" ", "_", $value["name"]);
                    $extension = pathinfo($value["name"]);
                    $dOficina = (isset($data["oficina"]) && $data["oficina"] != "") ? $oficina[$data["oficina"]].'/' : '';
                    $dCliente = (isset($data["idCliente"]) && $data["idCliente"] > 0) ? Cliente::getDocumento($data["idCliente"]).'/' : '';
                    $dCobertura = (isset($data["idCobertura"]) && $data["idCobertura"] > 0) ? $data["idCobertura"].'/'.Producto::getDominio($data["idCobertura"]).'/' : '';
                    $carpeta = $dOficina.$dCliente.$dCobertura;
                    $path = File::absolutePath().'biblioteca/archivo/'.$carpeta;
                    if(!file_exists($path)){
                        if(!mkdir($path, 0755, true)){
                            $mensaje['tipo'] = 'danger';
                            $mensaje['mensaje'] = 'Hubo un error al crear la carpeta contenedora de archivos. <b>Contante al administrador.</b>';
                            Alert::mensaje($mensaje);
                            exit;
                        }
                    }
                    if(file_exists($path.$name)){
                        $try = 0;
                        do{
                            $try++;
                            $name = '['.uniqid().']'.$name;
                        }while(file_exists($path.$name) && $try <= 10);
                        if($try >= 10){
                            $mensaje['tipo'] = 'warning';
                            $mensaje['mensaje'] = 'Hubo un error con el nombre del archivo ['.$value["name"].'], al parecer se encuentra repetido. <b>Intente nuevamente con otro nombre, o contacte al administrador.</b>';
                            Alert::mensaje($mensaje);
                        }
                    }
                    if(!move_uploaded_file($value["tmp_name"], $path.$name)){
                        $mensaje['tipo'] = 'danger';
                        $mensaje['mensaje'] = 'Hubo un error al mover el archivo ['.$name.']. Intente nuevamente, si el problema persiste, <b>contacte al admonistrador</b>.';
                        Alert::mensaje($mensaje);
                        exit;
                    }
                    $response[$key]["url"] = $carpeta.$name;
                }
                return $response;
            }else{
                return null;
            }
        }

        public static function administracionClienteFacturacionPath($idCompañia){ return File::absolutePath()."administracion/documentacion/compania/".$idCompañia."/facturacion/"; }

        public static function upload($file, $url){
            if(isset($file) && is_array($file) && count($file) > 0){
                $uploaded_file_response = [];
                foreach($_FILES AS $data){
                    $flag = 1;
                    $wFile = new SplFileInfo($data["name"]);
                    $file_name = str_replace(" ", "_", $data['name']);
                    $file_size =$data['size'];
                    $file_tmp = $data['tmp_name'];
                    $file_type = $data['type'];
                    $file_ext = $wFile->getExtension();
                    if(!file_exists($url)){
                        if(!mkdir($url, 0755, true)){
                            $mensaje['tipo'] = 'danger';
                            $mensaje['mensaje'] = 'Hubo un error al crear la carpeta contenedora de archivos. <b>Contante al administrador.</b>';
                            Alert::mensaje($mensaje);
                            exit;
                        }
                    }
                    if(File::checkExtension(mb_strtolower($file_ext))){
                        if(File::checkSize($file_size)){
                            if(File::checkExist($url.$file_name)){
                                $file_name = uniqid().'.'.$file_ext;
                            }
                            if(!File::move($file_tmp, $url.$file_name)){
                                $flag = 0;
                                $mensaje['tipo'] = 'danger';
                                $mensaje['cuerpo'] = 'Hubo un error al registrar el archivo '.$file_name.'. <b>Intente nuevamente o contacte al administrador.</b> ';
                                Alert::mensaje($mensaje);
                                exit;
                            }
                        }else{
                            $flag = 0;
                            $mensaje['tipo'] = 'warning';
                            $mensaje['cuerpo'] = 'El tamaño del archivo supera el permitido. <b>Intente nuevamente o contacte al administrador.</b>';
                            Alert::mensaje($mensaje);
                            exit;
                        }
                    }else{
                        $flag = 0;
                        Sistema::debug('error', 'file.class.php - upload - Error de extensión. Ref.: '.$file_ext);
                        $mensaje['tipo'] = 'warning';
                        $mensaje['cuerpo'] = 'La extensión del archivo no se encuentra entre las permitidas para ser procesadas por el sistema. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                        exit;
                    }
                    $uploaded_file_response[] = ["file" => $file_name, "estado" => $flag];
                }
                return $uploaded_file_response;
            }else{
                $mensaje['tipo'] = 'danger';
                $mensaje['cuerpo'] = 'Hubo un error al recibir los archivos para registrar. <b>Intente nuevamente o contacte al administrador.</b>';
                Alert::mensaje($mensaje);
                exit;
            }
        }

        public static function move($file_tmp, $urlFull){ return (move_uploaded_file($file_tmp, $urlFull)) ? true : false; }

        public static function checkExist($urlFull){ return (file_exists($urlFull)) ? true : false; }

        public static function checkSize($file_size, $permitido = 2697152){ return ($file_size > 2697152) ? false : true; }

        public static function checkExtension($file_extension, $extensions = ["jpeg","jpg","png","xlsx","doc","docx","xls","pdf"]){ return (in_array($file_extension,$extensions) === false) ? false : true; }
    }
?>