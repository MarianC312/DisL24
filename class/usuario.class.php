<?php
    class Usuario{

        private $id, $nombre, $compañia, $sucursal, $rol, $email, $estado, $debug = false, $auth = false;
        private $debugTipo = [
            0 => "all",
            1 => "success",
            2 => "info",
            3 => "alert",
            4 => "error",
            null => "no"
        ];

        function __construct($data, $auth){
            $this->id = $data["id"];
            $this->nombre = $data["nombre"];
            $this->compañia = $data["compañia"];
            $this->sucursal = $data["sucursal"];
            $this->rol = $data["rol"];
            $this->email = $data["email"];
            $this->estado = $data["estado"];
            $this->debug = $this->debugTipo[$data["debug"]];
            $this->auth = $auth;
        }

        function getAuth(){ return $this->auth; }
        function getId(){ return $this->id; }
        function getNombre(){ return $this->nombre; }
        function getCompañia(){ return $this->compañia; }
        function getSucursal(){ return $this->sucursal; }
        function getEmail(){ return $this->email; }
        function getEstado(){ return $this->estado; }
        function getRol(){ return $this->rol; }
        function debug(){ return $this->debug; }

        function tarea($identificador, $data = null){
            if(isset($identificador) && !is_null($identificador) && strlen($identificador) > 0){
                Session::iniciar();
                if(!isset($_SESSION["tarea"])){
                    Sistema::debug("info", "usuario.class.php - tarea - Tarea no existente, ejecutando tareaCrear.");
                    $this->tareaCrear();
                }else{
                    Sistema::debug("info", "usuario.class.php - tarea - Tarea existe, ejecutando tareaAgregar.");
                    $this->tareaAgregar($identificador);
                }
                if(!is_null($data)){
                    $this->tareaAgregarData($identificador, $data);
                }
            }else{
                Sistema::debug("error", "usuario.class.php - tarea - Identificador erroneo.");
            }
        }

        function tareaAgregarData($tarea, $data, $crearTarea = false){
            if($this->tareaExiste($tarea)){
                if(is_array($data) && count($data) > 0){
                    foreach($data AS $key => $value){
                        $_SESSION["tarea"][$tarea]["data"][$key] = $value;
                    }
                    Sistema::debug("success", "usuario.class.php - tareaAgregarData - Información agregada a la tarea satisfactoriamente.");
                    return true;
                }else{
                    Sistema::debug("error", "usuario.class.php - tareaAgregarData - Información incorrecta.");
                    return false;
                }
            }else{
                if($crearTarea){
                    if($this->tareaAgregar($tarea)){
                        Sistema::debug("info", "usuario.class.php - tareaAgregarData - Callback.");
                        $this->tareaAgregarData($tarea, $data);
                    }else{
                        Sistema::debug("error", "usuario.class.php - tareaAgregarData - Error al agregar tarea.");
                        return false;
                    }
                }else{
                    Sistema::debug("error", "usuario.class.php - tareaAgregarData - La tarea no existe, no se logró agregar la información.");
                    return false;
                }
            }
        }

        function tareaAgregar($tarea){
            Session::iniciar();
            if(!$this->tareaExiste($tarea)){
                $_SESSION["tarea"][$tarea] = [];
                echo '<script>loadUsuarioTareasPendientes()</script>';
                Sistema::debug("info", "usuario.class.php - tareaAgregar - Tarea creada satisfactoriamente.");
                return true;
            }else{
                Sistema::debug("info", "usuario.class.php - tareaAgregar - Tarea existente.");
                return null;
            }
        }

        function tareaExiste($tarea){
            Session::iniciar();
            Sistema::debug("info", "usuario.class.php - tareaExiste - Ejecutado correctamente. Return -> '".((isset($_SESSION["tarea"][$tarea]) && is_array($_SESSION["tarea"][$tarea])) ? 1 : 0)."'");
            return (isset($_SESSION["tarea"][$tarea]) && is_array($_SESSION["tarea"][$tarea])) ? true : false;
        }

        function tareaCrear(){
            Session::iniciar();
            if(!isset($_SESSION["tarea"])){
                $_SESSION["tarea"] = [];
                Sistema::debug("success", "usuario.class.php - tareaCrear - Registro de tareas creado satisfactoriamente.");
            }else{
                Sistema::debug("success", "usuario.class.php - tareaCrear - Registro de tareas existente.");
            }
        }

        function getInfoEstado(){
            $response = [
                "estado" => $this->getEstado()
            ];
            return $response;
        }

        function reloadStaticData(){
            Session::iniciar();
            $response = $this->getData($this->getEmail());
            $_SESSION["usuario"] = New Usuario($response, $this->getEstado());
        }

        function getInfo($idUsuario = null){
            $rol = Lista::rol();
            $compañia = Compania::getNombre($this->getCompañia());
            $sucursal = Compania::sucursalGetNombre($this->getSucursal());
            $response = [
                "id" => $this->getId(),
                "nombre" => $this->getNombre(),
                "compañia" => (strlen($compañia) > 0) ? $compañia : "Error",
                "compañiaId" => $this->getCompañia(),
                "sucursal" => (strlen($sucursal) > 0) ? $sucursal : "Error",
                "sucursalId" => $this->getSucursal(),
                "rol" => $rol[$this->getRol()],
                "rolId" => $this->getRol(),
                "email" => $this->getEmail(),
                "estado" => $this->getEstado(),
                "auth" => $this->getAuth(),
                "debug" => $this->debug()
            ];
            return $response;
        }

        public static function logout(){
            Session::iniciar();
            session_destroy();
            echo '<meta http-equiv="refresh" content="1;URL=./index.php" />';
        }

        public static function login($data){
            $response = null;
            if(isset($data) && is_array($data) && count($data) == 2){
                $response = Usuario::getData($data["email"]);
                if(is_array($response)){
                    return (Sistema::hashGet($data["pass"]) === $response["pass"]) ? New Usuario($response, true) : New Usuario($response, false);
                }else{
                    return $response;
                }
            }else{
                return $response;
            }
        } 



        private static function getData($email){
            if(isset($email) && !is_null($email) && strlen($email) >= 7 && $email != ""){
                $query = DataBase::select("usuario", "*", "email = '".$email."' AND estado = 1", "");
                if($query){
                    if(DataBase::getNumRows($query) == 1){
                        $dataQuery = DataBase::getArray($query);
                        foreach($dataQuery AS $key => $value){
                            if(is_int($key)){
                                unset($dataQuery[$key]);
                            }
                        }
                        return $dataQuery;
                    }else{
                        return DataBase::getNumRows($query);
                    }
                }else{
                    return false;
                }
            }else{
                return null;
            } 
        }
    }
?>