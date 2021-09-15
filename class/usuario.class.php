<?php
    class Usuario{

        private $id, $nombre, $compañia, $sucursal, $rol, $email, $estado, $admin = false, $actividadJornada, $actividadCaja, $actividadFechaInicio, $actividadFechaFin, $debug = false, $auth = false, $lastReloadStaticData, $lastReloadFEStaticData, $lastTicketCheck;
        private $debugTipo = [
            0 => "all",
            1 => "success",
            2 => "info",
            3 => "alert",
            4 => "error",
            null => "no"
        ];

        function __construct($data, $auth, $reload = false){
            $this->id = $data["id"];
            $this->nombre = $data["nombre"];
            $this->compañia = $data["compañia"];
            $this->sucursal = $data["sucursal"];
            $this->rol = $data["rol"];
            $this->email = $data["email"];
            $this->estado = $data["estado"];
            $this->admin = $data["admin"];
            $this->actividadJornada = $data["actividadJornada"];
            $this->actividadCaja = $data["actividadCaja"];
            $this->actividadFechaInicio = $data["actividadFechaInicio"];
            $this->actividadFechaFin = $data["actividadFechaFin"];
            $this->debug = $this->debugTipo[$data["debug"]];
            $this->auth = $auth;
            $this->lastReloadStaticData = ($reload) ? $this->lastReloadStaticData : null; 
            $this->lastReloadFEStaticData = ($reload) ? $this->lastReloadFEStaticData : null; 
            $this->lastTicketCheck = ($reload) ? $this->lastTicketCheck : Centroayuda::$publicData["ultimaRevision"]; 
        }

        function getAuth(){ return $this->auth; }
        function getId(){ return $this->id; }
        function getNombre(){ return $this->nombre; }
        function getCompañia(){ return $this->compañia; }
        function getSucursal(){ return $this->sucursal; }
        function getEmail(){ return $this->email; }
        function getEstado(){ return $this->estado; }
        function getRol(){ return $this->rol; }
        function getActividadCaja(){ return $this->actividadCaja; }
        function getActividadFechaInicio(){ return $this->actividadFechaInicio; }
        function getActividadFechaFin(){ return $this->actividadFechaFin; }
        function getActividadJornada(){ return $this->actividadJornada; }
        function getLastReloadStaticData(){ return $this->lastReloadStaticData; }
        function getLastReloadFEStaticData(){ return $this->lastReloadFEStaticData; }
        function getLastTicketCheck(){ return $this->lastTicketCheck; }
        function getCajaData(){
            $this->setDataActividadCaja();
            return [
                "actividadJornada" => $this->actividadJornada,
                "actividadCaja" => $this->actividadCaja,
                "actividadFechaInicio" => $this->actividadFechaInicio,
                "actividadFechaFin" => $this->actividadFechaFin
            ];
        }

        function isAdmin(){ return $this->admin; }
        function debug(){ return $this->debug; }

        function setActividadJornada($idJornada){ $this->actividadJornada = $idJornada; }
        function setActividadCaja($idCaja){ $this->actividadCaja = $idCaja; }
        function setActividadFechaInicio($fecha){ $this->actividadFechaInicio = $fecha; }
        function setActividadFechaFin($fecha){ $this->actividadFechaFin = $fecha; }
        function setLastReloadStaticData(){ $this->lastReloadStaticData = Date::current(); }
        function setLastReloadFEStaticData(){ $this->lastReloadFEStaticData = Date::current(); }
        function setLastTicketCheck(){ $this->lastTicketCheck = Date::current(); }

        function shouldReloadStaticData(){
            $date1 = $this->getLastReloadStaticData();
            //$dataProducto = Sistema::dataBaseProductoCodificadoUpdate($_SESSION["usuario"]->getLastReloadStaticData(), true);
            //$dataProductoStock = Sistema::dataBaseProductoStock($_SESSION["usuario"]->getLastReloadStaticData(), true);
            if(is_null($date1)){ //  || (is_array($dataProducto) && count($dataProducto) > 0) || (is_array($dataProductoStock) && count($dataProductoStock) > 0)
                return true;
            }else{ 
                $date2 = Date::current();
                $timestamp1 = strtotime($date1);
                $timestamp2 = strtotime($date2);
                $minutes = abs($timestamp2 - $timestamp1)/(60);
                return ($minutes >= 60) ? true : false; //60
            }
        }

        function shouldReloadFEStaticData(){
            $date1 = $this->getLastReloadFEStaticData();
            //$dataProducto = Sistema::dataBaseProductoCodificadoUpdate($_SESSION["usuario"]->getLastReloadStaticData(), true);
            //$dataProductoStock = Sistema::dataBaseProductoStock($_SESSION["usuario"]->getLastReloadStaticData(), true);
            if(is_null($date1)){ //  || (is_array($dataProducto) && count($dataProducto) > 0) || (is_array($dataProductoStock) && count($dataProductoStock) > 0)
                return true;
            }else{ 
                $date2 = Date::current();
                $timestamp1 = strtotime($date1);
                $timestamp2 = strtotime($date2);
                $minutes = abs($timestamp2 - $timestamp1)/(60);
                return ($minutes >= 60) ? true : false; //60
            }
        }

        function updateActividadJornada($idJornada){
            if(Sistema::usuarioLogueado()){
                if(isset($idJornada) && is_numeric($idJornada) && $idJornada > 0){
                    $query = DataBase::update("usuario", "actividadJornada = '".$idJornada."'", "id = '".$this->getId()."'");
                    if($query){
                        $this->setDataActividadCaja();
                        return true;
                    }else{
                        Sistema::debug('error', 'usuario.class.php - updateActividadJornada - Error al actualizar datos de jornada. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'usuario.class.php - updateActividadJornada - Error en identificador de jornada. Ref.: '.$idJornada);
                }
            }else{
                Sistema::debug('error', 'usuario.class.php - updateActividadJornada - Usuario no logueado.');
            }
            return false;
        }

        function setDataActividadCaja(){
            if(Sistema::usuarioLogueado()){
                $query = DataBase::select("usuario", "actividadJornada,actividadCaja,actividadFechaInicio,actividadFechaFin", "id = '".$this->getId()."'", "");
                if($query){
                    if(DataBase::getNumRows($query) == 1){
                        $dataQuery = DataBase::getArray($query);
                        $this->setActividadJornada($dataQuery["actividadJornada"]);
                        $this->setActividadCaja($dataQuery["actividadCaja"]);
                        $this->setActividadFechaInicio($dataQuery["actividadFechaInicio"]);
                        $this->setActividadFechaFin($dataQuery["actividadFechaFin"]);
                        return true;
                    }else{
                        Sistema::debug('error', 'usuario.class.php - setDataActividadCaja - No se encontró la información del usuario. Ref.: '.DataBase::getNumRows($query));
                    }
                }else{
                    Sistema::debug('error', 'usuario.class.php - setDataActividadCaja - Error al consultar la información de actividad del usuario. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('error', 'usuario.class.php - setDataActividadCaja - Usuario no logueado.');
            }
            return false;
        }

        function actividadCajaLimpiar(){
            if(Sistema::usuarioLogueado()){
                $query = DataBase::update("usuario", "actividadJornada = NULL, actividadCaja = NULL, actividadFechaInicio = NULL, actividadFechaFin = NULL", "id = '".$this->getId()."'");
                if($query){
                    $this->setDataActividadCaja();
                    return true;
                }else{
                    Sistema::debug('error', 'usuario.class.php - actividadCajaLimpiar - Error al limpiar los datos de actividad del usuario. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('error', 'usuario.class.php - actividadCajaLimpiar - Usuario no logueado.');
            }
            return false;
        }

        function actividadCajaInicio($data){
            if(Sistema::usuarioLogueado()){
                if(Caja::corroboraLibre($data["actividadCaja"])){
                    if(isset($data) && is_array($data) && count($data) == 1){
                        $query = DataBase::update("usuario", "actividadCaja = '".$data["actividadCaja"]."'", "id = '".$this->getId()."'");
                        if($query){
                            $this->setDataActividadCaja();
                            return true;
                        }else{
                            Sistema::debug('error', 'usuario.class.php - actividadCajaInicio - Error al updatear la información del usuario. Ref.: '.DataBase::getError());
                        }
                    }else{
                        Sistema::debug('error', 'usuario.class.php - actividadCajaInicio - Error en la información recibida. Ref.: '.count($data));
                    }
                }else{
                    Sistema::debug('error', 'usuario.class.php - actividadCajaInicio - Caja ocupada. Ref.: '.$data["actividadCaja"]);
                }
            }else{
                Sistema::debug('error', 'usuario.class.php - actividadCajaInicio - Usuario no logueado.');
            }
            return false;
        }

        function tarea($identificador, $data = null){
            if(isset($identificador) && !is_null($identificador) && strlen($identificador) > 0){
                Session::iniciar();
                if(!isset($_SESSION["tarea"])){
                    Sistema::debug("info", "usuario.class.php - tarea - Tarea no existente, ejecutando tareaCrear.");
                    $this->tareaCrear($identificador);
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

        function tareaAgregarData($tarea, $data, $crearTarea = true){
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
                echo '<script>tareasPendientesLoadHeader(true);</script>';
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

        function tareaEliminar($tarea, $refresh = true){
            Session::iniciar();
            if($this->tareaExiste($tarea)){ 
                Sistema::debug("info", "usuario.class.php - tareaEliminar - Ejecutando eliminación de tarea pendiente.");
                unset($_SESSION["tarea"][$tarea]);
                echo ($refresh) ? '<script>tareasPendientesLoadHeader(true);</script>' : '';
                return true;
            }else{ 
                Sistema::debug("info", "usuario.class.php - tareaEliminar - No se encontró la tarea <b>".$tarea."</b>."); 
                return false;
            }
            
        }

        function tareaCrear($tarea){
            Session::iniciar();
            if(!isset($_SESSION["tarea"])){
                $_SESSION["tarea"] = [];
                $_SESSION["tarea"][$tarea] = [];
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
            $_SESSION["usuario"] = New Usuario($response, $this->getEstado(), true);
        }

        function getInfo($idUsuario = null, $row = null){
            $rol = Lista::usuarioRol();
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
                "admin" => $this->isAdmin(),
                "email" => $this->getEmail(),
                "estado" => $this->getEstado(),
                "dataCaja" => $this->getCajaData(),
                "auth" => $this->getAuth(),
                "debug" => $this->debug()
            ];
            return (!is_null($row) && array_key_exists($row, $response)) ? $response[$row] : $response;
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