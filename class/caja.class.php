<?php
    class Caja{ 

        public static function dataGetMonto($idCaja, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idCaja) && is_numeric($idCaja) && $idCaja > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_caja", "monto", "id = '".$idCaja."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return $dataQuery["monto"];
                        }else{
                            Sistema::debug('error', 'caja.class.php - dataGetMonto - No se encontró la información de la caja. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'caja.class.php - dataGetMonto - Error al consultar la información. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - dataGetMonto - Identificador de caja erroneo. Ref.: '.$idCaja);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - dataGetMonto - Usuario no logueado.');
            }
            return false;
        }

        public static function historialGetAccionTipo($id){
            if(Sistema::usuarioLogueado()){
                if(isset($id) && is_numeric($id) && $id > 0){
                    $query = DataBase::select("compañia_sucursal_caja_historial", "tipo", "id = '".$id."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return $dataQuery["tipo"];
                        }else{
                            Sistema::debug('error', 'caja.class.php - historialGetAccionTipo - No se encontró el movimiento. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'caja.class.php - historialGetAccionTipo - Error al consultar la información. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - historialGetAccionTipo - Error en el identificador recibido. Ref.: '.$id);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - historialGetAccionTipo - Usuario no logueado.');
            }
            return false;
        }

        public static function update($idCaja, $monto, $accionId, $operador = null, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($monto) && is_numeric($monto) && $monto >= 0 && isset($accionId) && is_numeric($accionId) && $accionId > 0){
                    Session::iniciar();
                    $getCajaAccionTipo = Caja::historialGetAccionTipo($accionId);
                    $cajaAccionTipo = Lista::cajaAccionTipo();
                    if(!is_bool($getCajaAccionTipo) && is_numeric($getCajaAccionTipo)){
                        foreach($cajaAccionTipo AS $key => $value){
                            if($getCajaAccionTipo == $value["id"]){
                                switch($value["actividad"]){
                                    case 1:
                                        $query = DataBase::update("compañia_sucursal_caja", "monto = (monto + '".$monto."'), accion = '".$accionId."', operador = '".((is_numeric($operador)) ? $operador : $_SESSION["usuario"]->getId())."'", "id = '".$idCaja."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'"); 
                                    break 1;
                                    case 2:
                                        $cajaMonto = Caja::dataGetMonto($idCaja, $sucursal, $compañia);
                                        if($cajaMonto >= $monto){
                                            $query = DataBase::update("compañia_sucursal_caja", "monto = (monto - '".$monto."'), accion = '".$accionId."', operador = '".((is_numeric($operador)) ? $operador : $_SESSION["usuario"]->getId())."'", "id = '".$idCaja."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'"); 
                                        }else{
                                            Sistema::debug('error', 'caja.class.php - update - No tiene fondos suficientes. Ref.: $'.$cajaMonto);
                                            return false;
                                        }
                                    break 1;
                                    case 4:
                                        $query = true;
                                    break 1;
                                    default:
                                        $query = false;
                                        Sistema::debug('error', 'caja.class.php - update - No se reconoce la actividad a realizar. Ref.: '.$value["actividad"]);
                                    break 1;
                                }
                                break 1;
                            }
                        }
                        if($query){
                            Sistema::debug('info', 'caja.class.php - update - success!');
                            return true;
                        }else{
                            Sistema::debug('error', 'caja.class.php - update - No se pudo realizar el movimiento. Ref.: '.DataBase::getError());
                            return false;
                        }
                        Sistema::debug('error', 'caja.class.php - update - No se reconoce el movimiento. Ref.: '.$getCajaAccionTipo);
                    }else{
                        Sistema::debug('error', 'caja.class.php - update - Error en el tipo de acción recibido. Ref.: '.$getCajaAccionTipo);  
                    } 
                }else{
                    Sistema::debug('error', 'caja.class.php - update - Error en los datos recibidos.');
                }
            }else{
                Sistema::debug('error', 'caja.class.php - update - Usuario no logueado.');
            }
            return false;
        }

        public static function accionRegistrar($data, $alert = true, $update = true){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    if(Caja::corroboraAcceso($data["idCaja"]) || $data["tipo"] == 6){
                        $cajaAccionTipo = Lista::cajaAccionTipo();
                        foreach($cajaAccionTipo AS $key => $value){
                            if($data["tipo"] == $value["id"]){
                                if($value["actividad"] == 2){ 
                                    $cajaMonto = Caja::dataGetMonto($data["idCaja"], $_SESSION["usuario"]->getSucursal(), $_SESSION["usuario"]->getCompañia());
                                    if($cajaMonto < $data["monto"]){
                                        $mensaje['tipo'] = 'info';
                                        $mensaje['cuerpo'] = 'No tiene fondos suficientes para realizar el movimiento.';
                                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-info">Regresar</button></div>';
                                        Alert::mensaje($mensaje);
                                        exit;
                                    }
                                }
                                $query = DataBase::insert("compañia_sucursal_caja_historial", "caja,tipo,observacion,monto,venta,operador,sucursal,compañia", "'".$data["idCaja"]."','".$data["tipo"]."','".$data["observacion"]."','".$data["monto"]."',".((isset($data["venta"]) && is_numeric($data["venta"]) && $data["venta"] > 0) ? "'".$data["venta"]."'" : "NULL").",'".$_SESSION["usuario"]->getId()."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."'"); 
                                break;
                            }
                        }
                        if($query){
                            $mensaje['tipo'] = 'success';
                            $mensaje['cuerpo'] = 'Se registró el movimiento satisfactoriamente.';
                            if($alert) Alert::mensaje($mensaje);
                            $data["operador"] = $_SESSION["usuario"]->getId();
                            $data["sucursal"] = $_SESSION["usuario"]->getSucursal();
                            $data["compañia"] = $_SESSION["usuario"]->getCompañia();
                            $data["identificador"] = DataBase::getLastId();
                            Sistema::compañiaSucursalCajaUpdate($data);
                            if($update){
                                echo '<script>setTimeout(() => { cajaUpdateMonto('.Caja::dataGetMonto($data["idCaja"]).') }, 350)</script>';
                                echo '<script>setTimeout(() => { cajaHistorial('.$data["idCaja"].') }, 350)</script>';
                            }
                            return true;
                        }else{
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al registrar el movimiento en caja. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            if($alert) Alert::mensaje($mensaje);
                            Sistema::debug('error', 'caja.class.php - accionRegistrar - Error al registrar la información en base de datos. Ref.: '.DataBase::getError()); 
                        }
                        $mensaje['tipo'] = 'warning';
                        $mensaje['cuerpo'] = 'No se reconoce el movimiento a realizar. <b>Intente nuevamente o contacte administrador.</b>';
                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                        if($alert) Alert::mensaje($mensaje);
                        Sistema::debug('alert', 'caja.class.php - accionRegistrar - No se reconoce el movimiento. Ref.: '.$data["tipo"]);
                    }else{
                        $mensaje['tipo'] = 'warning';
                        $mensaje['cuerpo'] = 'Hubo un error al comprobar la caja de trabajo. No se registró el movimiento. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con la información recibida. <b>Intente nuevamente o contacte al administrador.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                    if($alert) Alert::mensaje($mensaje);
                    Sistema::debug('error', 'caja.class.php - accionRegistrar - Error en el arreglo de datos.');
                }
            }else{
                Sistema::debug('error', 'caja.class.php - accionRegistrar - Usuario no logueado.');
            }
            return false;
        }

        public static function corroboraLibre($idCaja, $idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idCaja) && is_numeric($idCaja) && $idCaja > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_caja", "locked", "id = '".$idCaja."' AND sucursal = '".((is_numeric($idSucursal)) ? $idSucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return (is_null($dataQuery["locked"]) || $dataQuery["locked"] == 0) ? true : false;
                        }else{
                            Sistema::debug('info', 'caja.class.php - corroboraLibre - No se encontró la caja. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'caja.class.php - corroboraLibre - Error al comprobar la información de la caja. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - corroboraLibre - Error en el identificador de caja. Ref.: '.$idCaja);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - corroboraLibre - Usuario no logueado.');
            }
            return false;
        }

        public static function bloquear($idCaja, $lock = 1, $accion = null, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idCaja) && is_numeric($idCaja) && $idCaja > 0){
                    Session::iniciar();
                    $query = DataBase::update("compañia_sucursal_caja","locked = '".$lock."', accion = ".((is_null($accion)) ? "NULL" : "'".$accion."'").", operador = '".$_SESSION["usuario"]->getId()."'", "id = '".$idCaja."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'");
                    if($query){
                        return true;
                    }else{
                        Sistema::debug('error', 'caja.class.php - bloquear - Error al bloquear la caja. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - bloquear - Error en el identificador de caja. Ref.: '.$idCaja);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - bloquear - Usuario no logueado.');
            }
            return false;
        }

        public static function actividadRegistro($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    switch($data["actividad"]){
                        case 1:
                            Session::iniciar();
                            if(Compania::cajaCorroboraExistencia($data["caja"])){
                                if(Caja::corroboraLibre($data["caja"])){
                                    if($_SESSION["usuario"]->actividadCajaInicio(["actividadCaja" => $data["caja"]])){ //caja y finicio pero sin jornada
                                        $bloqueaCaja = (Caja::bloquear($data["caja"])) ? true : false;
                                        $registraInicio = (Caja::accionRegistrar(["idCaja" => $data["caja"], "tipo" => 6, "observacion" => "Apertura de caja.", "monto" => Caja::dataGetMonto($data["caja"]) ], false, false)) ? true : false;
                                        $registraJornada = (Caja::jornadaRegistrar($data["caja"], $_SESSION["usuario"]->getActividadFechaInicio())) ? true : false;
                                        if($bloqueaCaja && $registraInicio && $registraJornada){ 
                                            echo '<script>setTimeout(() => { cajaGestion('.$data["caja"].','.$data["actividad"].') }, 350)</script>';
                                        }else{
                                            if(!Caja::bloquear($data["caja"], 0)){
                                                $mensaje['tipo'] = 'warning';
                                                $mensaje['cuerpo'] = 'Hubo un error al desbloquear la caja. <b>Intente nuevamente o contacte al administrador.</b>';
                                                Alert::mensaje($mensaje);
                                            }
                                            if(!$_SESSION["usuario"]->actividadCajaLimpiar()){
                                                $mensaje['tipo'] = 'warning';
                                                $mensaje['cuerpo'] = 'Hubo un error al limpiar las actividades del usuario. <b>Contacte al administrador a la brevedad.</b>';
                                                Alert::mensaje($mensaje);
                                            }
                                            if(!$bloqueaCaja){
                                                $mensaje['tipo'] = 'danger';
                                                $mensaje['cuerpo'] = 'Hubo un error al bloquear la caja para comenzar la actividad. <b>Intente nuevamente o contacte al administrador.</b>';
                                                Alert::mensaje($mensaje);
                                            }elseif(!$registraInicio){
                                                $mensaje['tipo'] = 'danger';
                                                $mensaje['cuerpo'] = 'Hubo un error al registrar el movimiento inicial de apertura de caja. <b>Intente nuevamente o contacte al administrador.</b>';
                                                Alert::mensaje($mensaje);
                                            }elseif(!$registraJornada){
                                                $mensaje['tipo'] = 'danger';
                                                $mensaje['cuerpo'] = 'Hubo un error al registrar la jornada. <b>Intente nuevamente o contacte al administrador.</b>';
                                                Alert::mensaje($mensaje);
                                            }else{
                                                $mensaje['tipo'] = 'warning';
                                                $mensaje['cuerpo'] = 'Error desconocido. <b>Contacte al administrador a la brevedad.</b>';
                                                Alert::mensaje($mensaje);
                                            }
                                            
                                        }
                                    }else{
                                        $mensaje['tipo'] = 'danger';
                                        $mensaje['cuerpo'] = 'Hubo un error al registrar la actividad del usuario sobre la caja seleccionada. <b>Intente nuevamente o contacte al administrador.</b>';
                                        Alert::mensaje($mensaje);
                                    }
                                }else{
                                    if($data["caja"] === $_SESSION["usuario"]->getActividadCaja() && $_SESSION["usuario"]->getActividadFechaInicio() != date("Y-m-d")){
                                        if(Caja::bloquear($data["caja"], 0)){
                                            if($_SESSION["usuario"]->actividadCajaLimpiar()){
                                                Caja::actividadRegistro($data);
                                            }else{
                                                $mensaje['tipo'] = 'warning';
                                                $mensaje['cuerpo'] = 'La caja se encontraba bloqueada. No se realizó el cierre correcto de caja y no se ha podido limpiar los datos de actividad del usuario. <b>Contacte al administrador a la brevedad.</b>';
                                                Alert::mensaje($mensaje);
                                            } 
                                        }else{
                                            $mensaje['tipo'] = 'warning';
                                            $mensaje['cuerpo'] = 'La caja se encuentra bloqueada. No se realizó el cierre correcto de caja y no se ha podido desbloquear la caja. <b>Contacte al administrador a la brevedad.</b>';
                                            Alert::mensaje($mensaje);
                                        }
                                    }else{
                                        $mensaje['tipo'] = 'warning';
                                        $mensaje['cuerpo'] = 'La caja seleccionada se encuentra ocupada por un operador. <b>Si considera esto un error, contacte al administrador a la brevedad.</b>';
                                        Alert::mensaje($mensaje);
                                    }
                                }
                            }else{
                                $mensaje['tipo'] = 'warning';
                                $mensaje['cuerpo'] = 'La caja seleccionada no pertenece a la sucursal o compañía. <b>Intente nuevamente o contacte al administrador.</b>';
                                Alert::mensaje($mensaje);
                            }
                            break;
                        case 2:
                            echo '<script>cajaGestion('.$data["caja"].','.$data["actividad"].')</script>';
                            break;
                        default:
                            echo '<script>cajaActividadFormulario()</script>';
                            break;
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - actividadRegistro - Error en la información recibida. Ref.: '.count($data));
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error en la información recibida. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - actividadRegistro - Usuario no logueado.');
            }
        }

        public static function actividadFormulario(){
            if(Sistema::usuarioLogueado()){
                $caja = Compania::sucursalCajaData();
                ?>
                <div id="container-caja-actividad-formulario" class="mine-container">
                    <div class="d-flex justify-content-between"> 
                        <div class="titulo">Seleccionar actividad en caja</div>
                        <button type="button" onclick="$('#container-caja-actividad-formulario').remove()" class="btn delete"><i class="fa fa-times"></i></button>
                    </div>
                    <div id="caja-actividad-process" style="display: none;"></div>
                    <form id="caja-actividad-form" action="./engine/caja/actividad-registro.php" form="#caja-actividad-form" process="#caja-actividad-process">
                        <div class="form-group">
                            <label for="actividad">Actividad</label>
                            <select class="form-control" id="actividad" name="actividad">
                                <option value=""> -- </option>
                                <option value="1">Apertura de caja</option>
                                <option value="2">Visualización de caja</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="caja">Cajas de sucursal</label>
                            <select class="form-control" id="caja" name="caja">
                                <option value=""> -- </option>
                                <?php
                                    if(is_array($caja) && count($caja) > 0){
                                        foreach($caja AS $key => $value){
                                            echo '<option value="'.$value["id"].'">Caja N° '.$value["id"].' ['.(($value["locked"] == 1) ? "Ocupada" : "Libre").']</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="button" onclick="cajaActividadRegistrar()" class="btn btn-success">Avanzar</button>
                        </div>
                    </form>
                </div>
                <?php
                exit;
            }else{
                Sistema::debug('error', 'caja.class.php - actividadInicioFormulario - Usuario no logueado.');
            }
        }

        public static function accionRegistrarFormulario($small = false){
            if(Sistema::usuarioLogueado()){
                Sistema::controlActividadCaja();
                if(Caja::corroboraAcceso()){
                    $idCaja = $_SESSION["usuario"]->getActividadCaja();
                    $accion = $_SESSION["lista"]["caja"]["accion"]["tipo"];
                    ?>
                    <div id="container-caja-accion-formulario" class="mine-container <?php echo ($small) ? "sm" : "" ?>">
                        <div class="d-flex justify-content-between"> 
                            <div class="titulo">Acción en caja N° <?php echo $idCaja ?></div>
                            <button type="button" onclick="$('#container-caja-accion-formulario').remove()" class="btn delete"><i class="fa fa-times"></i></button>
                        </div>
                        <div id="caja-accion-process" style="display: none"></div>
                        <form id="caja-accion-form" action="./engine/caja/accion-registrar.php" form="#caja-accion-form" process="#caja-accion-process">
                            <div class="d-flex justify-content-around">
                                <div class="form-group mb-0 mr-1">
                                    <label for="tipo">Tipo</label>
                                    <select class="form-control" id="tipo" name="tipo">
                                        <?php
                                            if(is_array($accion) && count($accion) > 0){
                                                foreach($accion AS $key => $value){
                                                    if($key >= 5) continue;
                                                    echo '<option value="'.$key.'">'.$value["accion"].'</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group flex-grow-1 mb-0 mr-1">
                                    <label for="observacion">Observación</label>
                                    <textarea class="form-control" id="observacion" name="observacion" rows="1"></textarea>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="control-label">Monto</label>
                                    <div class="form-group mb-0">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="text" class="form-control" id="monto" name="monto" placeholder="0.00">
                                        </div>
                                        <small class="text-muted">Montos decimales con ".". (Separador de miles automático)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group d-flex justify-content-right">
                                <button type="button" onclick="cajaAccionRegistrar(<?php echo $idCaja ?>)" class="btn btn-success">Registrar</button>
                            </div>
                        </form>
                    </div>
                    <?php
                }
            }else{
                Sistema::debug('error', 'caja.class.php - accionFormulario - Usuario no logueado.');
            }
        }

        public static function historialData($idCaja, $fechaInicio = null, $fechaFin = null, $operador = null, $sucursal = null, $compañia = null, $limit = 250){
            if(Sistema::usuarioLogueado()){
                if(isset($idCaja) && is_numeric($idCaja) && $idCaja > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_caja_historial", "*", "caja = '".$idCaja."' AND ".((!is_null($fechaInicio) && !is_null($fechaFin)) ? " fechaCarga BETWEEN '".$fechaInicio."' AND '".$fechaFin."' AND " : "")." ".((!is_null($operador)) ? " operador = '".$operador."' AND " : "")." sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "ORDER BY fechaCarga DESC LIMIT ".$limit);
                    if($query){
                        $data = [];
                        if(DataBase::getNumRows($query) > 0){
                            while($dataQuery = DataBase::getArray($query)){
                                $data[$dataQuery["id"]] = $dataQuery;
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
                        Sistema::debug('error', 'caja.class.php - historialData - Error al consultar la información del historial. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - historialData - Error en identificador de caja. Ref.: '.$idCaja);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - historialData - Usuario no logueado.');
            }
            return false;
        } 

        public static function historialDataGetVentaCaja($idVenta, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idVenta) && is_numeric($idVenta) && $idVenta > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_caja_historial", "caja", "venta = '".$idVenta."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return $dataQuery["caja"];
                        }else{
                            Sistema::debug('info', 'venta.class.php - historialDataGetVentaCaja - No se encontró información de la venta. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'venta.class.php - historialDataGetVentaCaja - Error al consultar la información de la venta. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error' , 'venta.class.php - historialDataGetVentaCaja - Identificador de venta incorrecto. Ref.: '.$idVenta);
                }
            }else{
                Sistema::debug('error', 'venta.class.php - historialDataGetVentaCaja - Usuario no logueado.');
            }
            return false;
        }

        public static function pagoRegistrar($data, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    //echo '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>'; 
                    Session::iniciar();
                    if(Caja::corroboraAcceso()){
                        $idCaja = $_SESSION["usuario"]->getActividadCaja();
                        if(is_numeric($idCaja) && $idCaja > 0 && Compania::cajaCorroboraExistencia($idCaja)){
                            $dataVenta = Venta::data($data["idVenta"]);
                            if(is_array($dataVenta) && count($dataVenta) > 0){
                                if($data["cliente"] === $dataVenta[$data["idVenta"]]["cliente"]){
                                    $dataCliente = Cliente::data(["filtroOpcion" => 3, "id" => $data["cliente"]], ["id","nombre"]);
                                    if(is_array($dataCliente) && count($dataCliente) > 0){
                                        if($data["total"] === $dataVenta[$data["idVenta"]]["total"]){
                                            if($dataVenta[$data["idVenta"]]["pago"] == 8){
                                                $credito = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["credito"];
                                                $dataCaja = [];
                                                $dataCaja["pago"] = $data["pago"];
                                                $dataCaja["contado"] = (isset($data["monto-contado"])) ? $data["monto-contado"] : NULL;
                                                $dataCaja["debito"] = (isset($data["monto-debito"])) ? $data["monto-debito"] : NULL;
                                                $dataCaja["credito"] = (isset($data["monto-credito"])) ? $data["monto-credito"] : NULL;
                                                $dataCaja["efectivo"] = (isset($data["monto-efectivo"])) ? $data["monto-efectivo"] : NULL;
                                                $dataCaja["financiacion"] = (isset($data["cuota"]) && ($dataCaja["pago"] == 3 || $dataCaja["pago"] == 5 || $dataCaja["pago"] == 6)) ? $data["cuota"] : NULL;
    
                                                if($dataCaja["pago"] == 3 || $dataCaja["pago"] == 5 || $dataCaja["pago"] == 6){
                                                    $totalCredito = $dataCaja["credito"] * $credito[$dataCaja["financiacion"]]["interes"];
                                                }else{
                                                    $totalCredito = 0;
                                                }
                        
                                                $dataCaja["total"] = round($dataCaja["contado"] + $dataCaja["debito"] + $totalCredito, 2); 
                                                
                                                $query = DataBase::update("compañia_sucursal_venta", "caja = ".(($dataVenta[$data["idVenta"]]["pedido"] == 1) ? "'".$idCaja."'" : "caja").", pago = '".$dataCaja["pago"]."', contado = ".((strlen($dataCaja["contado"]) > 0) ? "'".$dataCaja["contado"]."'" : "NULL").", debito = ".((strlen($dataCaja["debito"]) > 0) ? "'".$dataCaja["debito"]."'" : "NULL").", credito = ".((strlen($dataCaja["credito"]) > 0) ? "'".$dataCaja["credito"]."'" : "NULL").", efectivo = ".((strlen($dataCaja["efectivo"]) > 0) ? "'".$dataCaja["efectivo"]."'" : "NULL").", financiacion = ".((is_numeric($dataCaja["financiacion"])) ? "'".$dataCaja["financiacion"]."'" : "NULL" ).", total = '".$dataCaja["total"]."'", "id = '".$data["idVenta"]."' AND pago = 8 AND cliente = '".$data["cliente"]."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'");
                                                if($query){
                                                    if($dataCaja["pago"] == 1 || $dataCaja["pago"] == 4 || $dataCaja["pago"] == 5){
                                                        $cajaInsertData = [
                                                            "idCaja" => $idCaja,
                                                            "tipo" => 1,
                                                            "observacion" => "Cobro cliente ".$dataCliente[$data["cliente"]]["nombre"]." ticket #".$dataVenta[$data["idVenta"]]["nComprobante"].", condición cobro ".$_SESSION["lista"]["pago"][$dataCaja["pago"]]["pago"],
                                                            "monto" => ($dataCaja["pago"] == 1) ? $dataCaja["total"] : $dataCaja["contado"],
                                                            "venta" => $data["idVenta"]
                                                        ];
                                                        $cajaUpdate = Caja::accionRegistrar($cajaInsertData, false);
                                                        if($cajaUpdate){
                                                            $query = DataBase::update("compañia_sucursal_venta", "procesadoCaja = 1", "id = '".$data["idVenta"]."' AND sucursal = '".$_SESSION["usuario"]->getSucursal()."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                                                            if(!$query){
                                                                Sistema::debug('error', 'venta.class.php - registrar - Error al procesar venta. Ref.: '.$data["idVenta"]);
                                                            }
                                                        }else{
                                                            Sistema::debug('error', 'venta.class.php - registar - Error al registrar acción en caja.');
                                                        }
                                                    }
                                                    $mensaje['tipo'] = 'success';
                                                    $mensaje['cuerpo'] = 'Pago registrado satisfactoriamente!';
                                                    Alert::mensaje($mensaje);
                                                    if($dataVenta[$data["idVenta"]]["pedido"] == 1){
                                                        echo '<script>compañiaSucursalPedido()</script>';
                                                    }
                                                    echo '<script>setTimeout(() => { clienteRefreshUI('.$data["cliente"].',"#cliente-container-compra", true) }, 350)</script>';
                                                }else{
                                                    Sistema::debug('error', 'caja.class.php - pagoRegistrar - Error al registrar pago. Ref.: '.DataBase::getError());
                                                    $mensaje['tipo'] = 'danger';
                                                    $mensaje['cuerpo'] = 'Hubo un error al registrar el pago. <b>Intente nuevamente o contacte al administrador.</b>';
                                                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                                                    Alert::mensaje($mensaje);
                                                }
                                            }else{
                                                $mensaje['tipo'] = 'warning';
                                                $mensaje['cuerpo'] = 'La venta no se encuentra "a cuenta", no se puede procesar el pago. Si considera esto un error, <b>contacte al administrador.</b>';
                                                $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                                                Alert::mensaje($mensaje);
                                            }
                                        }else{
                                            $mensaje['tipo'] = 'warning';
                                            $mensaje['cuerpo'] = 'El total a pagar no coincide con el total ingresado. <b>Intente nuevamente o contacte al administrador.</b>';
                                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                                            Alert::mensaje($mensaje);
                                        }
                                    }else{
                                        $mensaje['tipo'] = 'danger';
                                        $mensaje['cuerpo'] = 'Hubo un error al comprobar la información del cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-error">Regresar</button></div>';
                                        Alert::mensaje($mensaje);
                                    }
                                }else{
                                    $mensaje['tipo'] = 'warning';
                                    $mensaje['cuerpo'] = 'El cliente seleccionado no coincide con el cliente de la cuenta. <b>Intente nuevamente o contacte al administrador.</b>';
                                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                                    Alert::mensaje($mensaje);
                                }
                            }else{
                                $mensaje['tipo'] = 'warning';
                                $mensaje['cuerpo'] = 'No se pudo comprobar la información de la venta. <b>Intente nuevamente o contacte al administrador.</b>';
                                $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                                Alert::mensaje($mensaje);
                            }
                        }else{
                            $mensaje['tipo'] = 'warning';
                            $mensaje['cuerpo'] = 'No se pudo comprobar la información de la caja. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        $mensaje['tipo'] = 'warning';
                        $mensaje['cuerpo'] = 'No se pudo comprobar el acceso a la caja para realizar el cobro. <b>Intente nuevamente o contacte al administrador.</b>';
                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - pagoRegistrar - Error en arreglo de datos recibido. Ref.: '.count($data));
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información del pago. <b>Intente nuevamente o contacte al administrador.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-error">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - pagoRegistrar - Usuario no logueado.');
            }
        }

        public static function pagoFormulario($data, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(Caja::corroboraAcceso()){
                    Session::iniciar();
                    $idCaja = $_SESSION["usuario"]->getActividadCaja();
                    if(is_numeric($idCaja) && $idCaja > 0 && Compania::cajaCorroboraExistencia($idCaja)){
                        $dataCliente = (is_numeric($data["idVenta"])) ? Cliente::data(["filtroOpcion" => 3, "id" => Venta::dataGetCliente($data["idVenta"])], ["id","documento","nombre"]) : $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["cliente"];
                        $credito = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["credito"];
                        $dataVenta = Venta::data($data["idVenta"]);
                        ?>
                        <div id="container-caja-pago" class="curtain">
                            <div class="mine-container">
                                <div class="d-flex justify-content-between">
                                    <div class="titulo">Procesar pago caja N° <?php echo $idCaja ?></div>
                                    <button class="btn delete" type="button" onclick="$('#container-caja-pago').remove()"><i class="fa fa-times"></i></button>
                                </div>
                                <div id="caja-pago-process" style="display: none"></div>
                                <form id="caja-pago-form" action="./engine/caja/pago-registrar.php" form="#caja-pago-form" process="#caja-pago-process">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex justify-content-between"> 
                                            <div class="w-50">
                                                <div class="d-flex flex-column">
                                                    <div class="form-group mr-2">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" onchange="ventaRegistrarFormularioUpdateBusquedaCliente()" id="tipoCliente" <?php echo (is_numeric($data["idVenta"])) ? "disabled" : "checked" ?>>
                                                            <label class="custom-control-label" for="tipoCliente" id="tipoClienteLabel"><?php echo (is_numeric($data["idVenta"])) ? "Cliente" : "Comprador ocasional" ?></label>
                                                        </div>
                                                    </div>
                                                    <div id="container-cliente" class="form-group flex-grow-1">
                                                        <label for="cliente" class="d-block"><i class="fa fa-search"></i> Buscar cliente</label>
                                                        <select class="form-control" id="cliente" name="cliente" onchange="updatePago()" <?php echo (is_numeric($data["idVenta"])) ? "readonly" : "" ?>>
                                                            <option value=""> - Buscar cliente - </option>
                                                            <?php
                                                                if(is_array($dataCliente) && count($dataCliente) > 0){
                                                                    foreach($dataCliente AS $key => $value){
                                                                        echo '<option value="'.$value["id"].'" '.((is_numeric($data["idVenta"]) && count($dataCliente) == 1) ? "selected" : "").'>['.$value["documento"].'] '.$value["nombre"].'</option>';
                                                                    }
                                                                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="pago">Forma de pago</label>
                                                    <select class="form-control" id="pago" name="pago">
                                                        <option value=""> - Seleccionar opción de pago - </option>
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["pago"]) && count($_SESSION["lista"]["pago"]) > 0){
                                                                foreach($_SESSION["lista"]["pago"] AS $key => $value){
                                                                    if($key == 8 && is_numeric($data["idVenta"])) continue; 
                                                                    echo '<option value="'.$key.'">'.$value["pago"].'</option>';
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div id="container-pago-1" class="d-none">
                                                    <div class="form-group">
                                                        <label class="control-label">Monto contado</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-contado" name="monto-contado" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Efectivo</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-efectivo" name="monto-efectivo" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="container-pago-2" class="d-none">
                                                    <div class="form-group">
                                                        <label class="control-label">Monto debito</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-debito" name="monto-debito" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="container-pago-3" class="form-group d-none">
                                                    <div class="form-group">
                                                        <label class="control-label">Monto crédito</label>
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-credito" name="monto-credito" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div> 
                                                    <label for="cuota">Cuotas</label>
                                                    <select class="form-control" id="cuota" name="cuota" disabled>
                                                        <?php
                                                            if(is_array($credito) && count($credito) > 0){
                                                                foreach($credito AS $key => $value){
                                                                    echo '<option value="'.$key.'" data-test="asd" data-interes="'.$value["interes"].'" data-cuotas="'.$value["cuotas"].'">'.$value["tipo"].'</option>';
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div id="container-pago-4" class="d-none">
                                                    <div class="form-group">
                                                        <label class="control-label">Monto contado</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-contado" name="monto-contado" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Monto débito</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-debito" name="monto-debito" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div> 
                                                    <div class="form-group">
                                                        <label class="control-label">Efectivo</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-efectivo" name="monto-efectivo" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="container-pago-5" class="d-none">
                                                    <div class="form-group">
                                                        <label class="control-label">Monto contado</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-contado" name="monto-contado" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column"> 
                                                        <div class="form-group">
                                                            <label class="control-label">Monto crédito</label>
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">$</span>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="monto-credito" name="monto-credito" min="0" placeholder="0.00" value="0" disabled>
                                                                </div>
                                                            </div>
                                                        </div> 
                                                        <div class="form-group">
                                                            <label for="cuota">Cuotas</label>
                                                            <select class="form-control" id="cuota" name="cuota" disabled>
                                                                <?php
                                                                    if(is_array($credito) && count($credito) > 0){
                                                                        foreach($credito AS $key => $value){
                                                                            echo '<option value="'.$key.'" data-test="asd" data-interes="'.$value["interes"].'" data-cuotas="'.$value["cuotas"].'">'.$value["tipo"].'</option>';
                                                                        }
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div> 
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Efectivo</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-efectivo" name="monto-efectivo" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="container-pago-6" class="d-none">
                                                    <div class="form-group">
                                                        <label class="control-label">Monto débito</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-debito" name="monto-debito" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column"> 
                                                        <div class="form-group">
                                                            <label class="control-label">Monto crédito</label>
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">$</span>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="monto-credito" name="monto-credito" min="0" placeholder="0.00" value="0" disabled>
                                                                </div>
                                                            </div>
                                                        </div> 
                                                        <div class="form-group">
                                                            <label for="cuota">Cuotas</label>
                                                            <select class="form-control" id="cuota" name="cuota" disabled>
                                                                <?php
                                                                    if(is_array($credito) && count($credito) > 0){
                                                                        foreach($credito AS $key => $value){
                                                                            echo '<option value="'.$key.'" data-test="asd" data-interes="'.$value["interes"].'" data-cuotas="'.$value["cuotas"].'">'.$value["tipo"].'</option>';
                                                                        }
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div> 
                                                    </div>
                                                </div>
                                                <div id="container-pago-7" class="d-none">
                                                    <div class="form-group">
                                                        <label class="control-label">Monto contado</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-contado" name="monto-contado" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Monto débito</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-debito" name="monto-debito" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div> 
                                                    <div class="d-flex flex-column"> 
                                                        <div class="form-group">
                                                            <label class="control-label">Monto crédito</label>
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">$</span>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="monto-credito" name="monto-credito" min="0" placeholder="0.00" value="0" disabled>
                                                                </div>
                                                            </div>
                                                        </div> 
                                                        <div class="form-group">
                                                            <label for="cuota">Cuotas</label>
                                                            <select class="form-control" id="cuota" name="cuota" disabled>
                                                                <?php
                                                                    if(is_array($credito) && count($credito) > 0){
                                                                        foreach($credito AS $key => $value){
                                                                            echo '<option value="'.$key.'" data-test="asd" data-interes="'.$value["interes"].'" data-cuotas="'.$value["cuotas"].'">'.$value["tipo"].'</option>';
                                                                        }
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div> 
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Efectivo</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-efectivo" name="monto-efectivo" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="container-pago-8" class="d-none">
                                                    <div class="form-group">
                                                        <label class="control-label">Monto</label>
                                                        <div class="form-group">
                                                            <div class="input-group mb-3">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">$</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="monto-contado" name="monto-contado" min="0" placeholder="0.00" value="0" disabled>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="w-50 d-flex flex-column justify-content-center align-items-center">
                                                <div class="d-flex justify-content-around w-100 p-1">
                                                    <div id="container-pre-total" class="p-1 text-center h4">
                                                        Total: <br>
                                                        <span class="font-weight-bold d-block w-100">
                                                            $ <span id="pre-total"><?php echo number_format($dataVenta[$data["idVenta"]]["total"], 2, ",", ".") ?></span>
                                                        </span>
                                                    </div>
                                                    <div id="container-pre-pagar" class="p-1 text-center h4">
                                                        A pagar: <br>
                                                        <span class="font-weight-bold d-block w-100">
                                                            $ <span id="pre-pagar"><?php echo number_format($dataVenta[$data["idVenta"]]["total"], 2, ",", ".") ?></span>
                                                        </span>
                                                    </div>
                                                    <div id="container-pre-vuelto" class="p-1 text-center h4">
                                                        Vuelto: <br>
                                                        <span class="font-weight-bold d-block w-100">
                                                            $ <span id="pre-vuelto">0</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div id="container-pre-obs" class="w-100 text-center text-muted h5"></div>
                                            </div>
                                        </div> 
                                    </div> 
                                    <div class="form-group d-none">
                                        <label class="col-form-label" for="idVenta">Identificador</label>
                                        <input type="text" class="form-control" value="<?php echo $data["idVenta"] ?>" id="idVenta" name="idVenta" readonly>
                                    </div>
                                    <div class="form-group d-none">
                                        <label class="col-form-label" for="total">Total</label>
                                        <input type="text" class="form-control" value="<?php echo (is_numeric($data["idVenta"])) ? $dataVenta[$data["idVenta"]]["total"] : $data["total"] ?>" id="total" name="total" readonly>
                                    </div>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-success" onclick="cajaPagoRegistrar(<?php echo $data['idVenta'] ?>)">Registrar pago</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <script>
                            var totalCuenta = <?php echo (is_numeric($data["idVenta"])) ? $dataVenta[$data["idVenta"]]["total"] : $data["total"] ?>;
                            $("#pago").on("change", (e) => {
                                ventaPagoReset(totalCuenta);
                                switch(parseInt($("#pago").val())){
                                    case 1:
                                        $("#container-pago-1").removeClass("d-none").find("*").removeAttr("disabled"); 
                                        total = parseFloat(totalCuenta).toFixed(2);
                                        $("#container-pago-1 #monto-contado").val(total).prop("readonly", true);
                                        break;
                                    case 2:
                                        $("#container-pago-2").removeClass("d-none").find("*").removeAttr("disabled"); 
                                        total = parseFloat(totalCuenta).toFixed(2);
                                        $("#container-pago-2 #monto-debito").val(total).prop("readonly", true);
                                        break;
                                    case 3:
                                        $("#container-pago-3").removeClass("d-none").find("*").removeAttr("disabled");
                                        total = parseFloat(totalCuenta).toFixed(2);
                                        $("#container-pago-3 #monto-credito").val(total).prop("readonly", true);
                                        setTimeout(() => { calculaPreTotal('container-pago-3', totalCuenta); }, 350);
                                        break;
                                    case 4:
                                        $("#container-pago-4").removeClass("d-none").find("*").removeAttr("disabled");
                                        break;
                                    case 5:
                                        $("#container-pago-5").removeClass("d-none").find("*").removeAttr("disabled");
                                        break;
                                    case 6:
                                        $("#container-pago-6").removeClass("d-none").find("*").removeAttr("disabled");
                                        break;
                                    case 7:
                                        alert("No disponible!");
                                        e.preventDefault();
                                        return;
                                        break;
                                    case 8: 
                                        $("#container-pago-8").removeClass("d-none").find("*").removeAttr("disabled");
                                        total = parseFloat(totalCuenta).toFixed(2);
                                        $("#container-pago-8 #monto-contado").val(total).prop("readonly", true);
                                        break;
                                    default:
                                        alert("Seleccione una opción válida para pagar...");
                                        break;
                                }
                            }); 

                            $("#container-pago-1 #monto-efectivo, #container-pago-4 #monto-efectivo, #container-pago-5 #monto-efectivo, #container-pago-7 #monto-efectivo").on("keypress", (e) => {
                                if((e.which >= 48 && e.which <= 57) || (e.which >= 96 && e.which <= 105) || e.which == 8 || e.which == 46){ 
                                    let efectivo, contado, vuelto;
                                    switch(parseInt($("#pago").val())){
                                        case 1:
                                            efectivo = parseFloat($("#container-pago-1 #monto-efectivo").val() + e.key).toFixed(2);
                                            contado = parseFloat($("#container-pago-1 #monto-contado").val()).toFixed(2);
                                            vuelto = parseFloat((efectivo - contado)).toFixed(2);
                                            break;
                                        case 4:
                                            efectivo = parseFloat($("#container-pago-4 #monto-efectivo").val() + e.key).toFixed(2);
                                            contado = parseFloat($("#container-pago-4 #monto-contado").val()).toFixed(2);
                                            vuelto = parseFloat((efectivo - contado)).toFixed(2);
                                            break;
                                        case 5: 
                                            efectivo = parseFloat($("#container-pago-5 #monto-efectivo").val() + e.key).toFixed(2);
                                            contado = parseFloat($("#container-pago-5 #monto-contado").val()).toFixed(2);
                                            vuelto = parseFloat((efectivo - contado)).toFixed(2);
                                            break;
                                        case 7:
                                            efectivo = parseFloat($("#container-pago-7 #monto-efectivo").val() + e.key).toFixed(2);
                                            contado = parseFloat($("#container-pago-7 #monto-contado").val()).toFixed(2);
                                            vuelto = parseFloat((efectivo - contado)).toFixed(2);
                                            break;
                                    }
                                    //console.log(efectivo + " - " + contado + " = " + vuelto);
                                    $("#pre-vuelto").html(vuelto);
                                }else{
                                    e.preventDefault();
                                    if(e.which == 188){
                                        alert("Para ingresar centavos usa el . [punto] en lugar de la , [coma]");
                                    }else{ 
                                        alert("No se admite el ingreso de esa tecla.");
                                    }
                                }
                            });

                            $("#container-pago-5 #monto-credito, #container-pago-6 #monto-credito, #container-pago-7 #monto-credito").on("keypress", (e) => {
                                if((e.which >= 48 && e.which <= 57) || (e.which >= 96 && e.which <= 105) || e.which == 8 || e.which == 46){
                                    let total, contado, debito, credito, resto;
                                    switch(parseInt($("#pago").val())){
                                        case 5:
                                            $("#container-pago-5 #monto-contado").val(0);
                                            $("#container-pago-5 #monto-efectivo").val(0)
                                            total = parseFloat(totalCuenta).toFixed(2);
                                            contado = parseFloat($("#container-pago-5 #monto-contado").val()).toFixed(2);
                                            credito = parseFloat($("#container-pago-5 #monto-credito").val() + e.key).toFixed(2);
                                            resto = parseFloat((total - contado - credito)).toFixed(2);
                                            //console.log(total + " - " + contado + " - " + credito + " = " + resto);
                                            if(resto < 0 && e.which != 8){
                                                e.preventDefault();
                                                alert("El valor a pagar no puede ser mayor al total.");
                                                return;
                                            }else{
                                                $("#pre-pagar").html(0);
                                                $("#container-pago-5 #monto-contado").val(resto).prop("max", resto);
                                                setTimeout(() => { calculaPreTotal('container-pago-5', totalCuenta); }, 350);
                                            }
                                            break;
                                        case 6:
                                            $("#container-pago-6 #monto-debito").val(0);
                                            $("#container-pago-6 #monto-efectivo").val(0)
                                            total = parseFloat(totalCuenta).toFixed(2);
                                            debito = parseFloat($("#container-pago-6 #monto-debito").val()).toFixed(2);
                                            credito = parseFloat($("#container-pago-6 #monto-credito").val() + e.key).toFixed(2);
                                            resto = parseFloat((total - debito - credito)).toFixed(2);
                                            //console.log(total + " - " + contado + " - " + credito + " = " + resto);
                                            if(resto < 0 && e.which != 8){
                                                e.preventDefault();
                                                alert("El valor a pagar no puede ser mayor al total.");
                                                return;
                                            }else{
                                                $("#pre-pagar").html(0);
                                                $("#container-pago-6 #monto-debito").val(resto).prop("max", resto);
                                                setTimeout(() => { calculaPreTotal('container-pago-6', totalCuenta); }, 350);
                                            }
                                            break;
                                        case 7:
                                            $("#container-pago-7 #monto-contado").val(0);
                                            $("#container-pago-7 #monto-efectivo").val(0);
                                            $("#container-pago-7 #monto-debito").val(0);
                                            total = parseFloat(totalCuenta).toFixed(2);
                                            contado = parseFloat($("#container-pago-5 #monto-contado").val()).toFixed(2);
                                            debito = parseFloat($("#container-pago-5 #monto-debito").val()).toFixed(2);
                                            credito = parseFloat($("#container-pago-5 #monto-credito").val() + e.key).toFixed(2);
                                            resto = parseFloat((total - contado - debito - credito)).toFixed(2);
                                            //console.log(total + " - " + contado + " - " + credito + " = " + resto);
                                            if(resto < 0 && e.which != 8){
                                                e.preventDefault();
                                                alert("El valor a pagar no puede ser mayor al total.");
                                                return;
                                            }else{
                                                $("#pre-pagar").html(0);
                                                $("#container-pago-5 #monto-contado").val(resto).prop("max", resto);
                                                setTimeout(() => { calculaPreTotal('container-pago-7', totalCuenta); }, 350);
                                            }
                                            break;
                                    }
                                }else{
                                    e.preventDefault();
                                    if(e.which == 188){
                                        alert("Para ingresar centavos usa el . [punto] en lugar de la , [coma]");
                                    }else{ 
                                        alert("No se admite el ingreso de esa tecla.");
                                    }
                                }
                            });

                            $("#container-pago-4 #monto-debito, #container-pago-5 #monto-debito, #container-pago-6 #monto-debito").on("keypress", (e) => {
                                if((e.which >= 48 && e.which <= 57) || (e.which >= 96 && e.which <= 105) || e.which == 8 || e.which == 46){
                                    let total, contado, debito, resto;
                                    switch(parseInt($("#pago").val())){
                                        case 4:
                                            $("#container-pago-4 #monto-contado").val(0);
                                            $("#container-pago-4 #monto-efectivo").val(0)
                                            total = parseFloat(totalCuenta).toFixed(2);
                                            contado = parseFloat($("#container-pago-4 #monto-contado").val()).toFixed(2);
                                            debito = parseFloat($("#container-pago-4 #monto-debito").val() + e.key).toFixed(2);
                                            resto = parseFloat((total - contado - debito)).toFixed(2);
                                            //console.log(total + " - " + contado + " - " + credito + " = " + resto);
                                            if(resto < 0 && e.which != 8){
                                                e.preventDefault();
                                                alert("El valor a pagar no puede ser mayor al total.");
                                                return;
                                            }else{
                                                $("#pre-pagar").html(0);
                                                $("#container-pago-4 #monto-contado").val(resto).prop("max", resto);
                                                setTimeout(() => { calculaPreTotal('container-pago-4', totalCuenta); }, 350);
                                            }
                                            break;
                                        case 6:
                                            $("#container-pago-6 #monto-efectivo").val(0);
                                            $("#container-pago-6 #monto-credito").val(0)
                                            total = parseFloat(totalCuenta).toFixed(2);
                                            credito = parseFloat($("#container-pago-6 #monto-credito").val()).toFixed(2);
                                            debito = parseFloat($("#container-pago-6 #monto-debito").val() + e.key).toFixed(2);
                                            resto = parseFloat((total - credito - debito)).toFixed(2);
                                            //console.log(total + " - " + contado + " - " + credito + " = " + resto);
                                            if(resto < 0 && e.which != 8){
                                                e.preventDefault();
                                                alert("El valor a pagar no puede ser mayor al total.");
                                                return;
                                            }else{
                                                $("#pre-pagar").html(0);
                                                $("#container-pago-6 #monto-credito").val(resto).prop("max", resto);
                                                setTimeout(() => { calculaPreTotal('container-pago-6', totalCuenta); }, 350);
                                            }
                                            break;
                                    }
                                }else{
                                    e.preventDefault();
                                    if(e.which == 188){
                                        alert("Para ingresar centavos usa el . [punto] en lugar de la , [coma]");
                                    }else{ 
                                        alert("No se admite el ingreso de esa tecla.");
                                    }
                                }
                            });

                            $("#container-pago-1 #monto-contado, #container-pago-4 #monto-contado, #container-pago-5 #monto-contado").on("keypress", (e) => { 
                                if((e.which >= 48 && e.which <= 57) || (e.which >= 96 && e.which <= 105) || e.which == 8 || e.which == 46){
                                    let total, contado, debito, resto;
                                    switch(parseInt($("#pago").val())){
                                        case 4:
                                            $("#container-pago-4 #monto-debito").val(0);
                                            $("#container-pago-4 #monto-efectivo").val(0);
                                            total = parseFloat(totalCuenta).toFixed(2);
                                            contado = parseFloat($("#container-pago-4 #monto-contado").val() + e.key).toFixed(2);
                                            debito = parseFloat($("#container-pago-4 #monto-debito").val()).toFixed(2);
                                            resto = parseFloat((total - contado - debito)).toFixed(2);
                                            if(resto < 0 && e.which != 8){
                                                e.preventDefault();
                                                alert("El valor a pagar no puede ser mayor al total.");
                                                return;
                                            }else{
                                                $("#pre-pagar").html(0);
                                                $("#container-pago-4 #monto-debito").val(resto).prop("max", resto);
                                                setTimeout(() => { calculaPreTotal('container-pago-4', totalCuenta); }, 350);
                                            }
                                            break;
                                        case 5:
                                            $("#container-pago-5 #monto-credito").val(0);
                                            $("#container-pago-5 #monto-efectivo").val(0);
                                            total = parseFloat(totalCuenta).toFixed(2);
                                            contado = parseFloat($("#container-pago-5 #monto-contado").val() + e.key).toFixed(2);
                                            credito = parseFloat($("#container-pago-5 #monto-credito").val()).toFixed(2);
                                            resto = parseFloat((total - contado - credito)).toFixed(2);
                                            console.log(total + " - " + contado + " - " + credito + " = " + resto);
                                            if(resto < 0 && e.which != 8){
                                                e.preventDefault();
                                                alert("El valor a pagar no puede ser mayor al total.");
                                                return;
                                            }else{
                                                $("#pre-pagar").html(0);
                                                $("#container-pago-5 #monto-credito").val(resto).prop("max", resto);
                                                setTimeout(() => { calculaPreTotal('container-pago-5', totalCuenta); }, 350);
                                            }
                                            break;
                                        case 7:

                                            break;
                                        default:
                                            console.log("no");
                                            break;
                                    }
                                    
                                }else{
                                    e.preventDefault();
                                    if(e.which == 188){
                                        alert("Para ingresar centavos usa el . [punto] en lugar de la , [coma]");
                                    }else{ 
                                        alert("No se admite el ingreso de esa tecla.");
                                    }
                                }
                            });

                            $("#container-pago-3 #cuota, #container-pago-5 #cuota, #container-pago-6 #cuota").on("change", (e) => { calculaPreTotal('container-pago-'+ $("#pago").val(), totalCuenta) });

                            function updatePago(){
                                setTimeout(() => { 
                                    let cliente = $("#cliente").val();
                                    if(typeof cliente == "string" && parseInt(cliente) > 0){
                                        $('#pago').children('option[value=8]').removeAttr('disabled');
                                    }else{
                                        $('#pago').children('option[value=8]').attr('disabled', true);
                                        tsCliente.reload();
                                        $("#container-cliente #cliente").val("");
                                    }
                                }, 375);
                            }
                            var idVenta = <?php echo $data["idVenta"] ?>;
                            if(isNaN(idVenta)){
                                var tsCliente = tailSelectSet("#cliente");
                            }
                            ventaRegistrarFormularioUpdateBusquedaCliente(totalCuenta);
                        </script>
                        <?php
                    }
                }
            }else{
                Sistema::debug('error', 'caja.class.php - pagoFormulario - Usuario no logueado.');
            }
        }

        public static function historial($idCaja, $actividad = 2, $small = false, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idCaja) && is_numeric($idCaja) && $idCaja > 0){
                    Session::iniciar();
                    $data = Caja::historialData($idCaja, null, null, null, null, null, ($small) ? 5 : 250);
                    ?>
                    <div class="mine-container sm">
                        <div class="titulo">Actividad caja #<?php echo $idCaja ?></div>
                        <div class="p-1">
                            <table id="tabla-caja-historial" class="table table-hover <?php echo (!$small) ? "table-responsive" : "" ?> w-100">
                                <thead>
                                    <tr>
                                        <td>Operador</td>
                                        <td>Tipo</td>
                                        <td>Monto $</td>
                                        <td>Verif.</td>
                                        <td class="text-right" style="width: fit-content">Acciones</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        if(is_array($data)){
                                            if(count($data) > 0){
                                                foreach($data AS $key => $value){
                                                    switch($_SESSION["lista"]["caja"]["accion"]["tipo"][$value["tipo"]]["actividad"]){
                                                        case 1:
                                                            $icon = "plus";
                                                            $operador = "+";
                                                            $color = "success";
                                                            break;
                                                        case 2:
                                                            $icon = "minus";
                                                            $operador = "-";
                                                            $color = "danger";
                                                            break;
                                                        case 3:
                                                            $icon = "exchange";
                                                            $operador = "-";
                                                            $color = "warning";
                                                            break;
                                                        case 4:
                                                            $icon = "check-square";
                                                            $operador = "";
                                                            $color = "info";
                                                            break;
                                                    }
                                                    ?>
                                                    <tr id="mov-<?php echo $key ?>" class="<?php echo ($value["estado"] != 1) ? "anulado" : "" ?>">
                                                        <td><?php echo $_SESSION["lista"]["operador"][$value["operador"]]["nombre"] ?></td>
                                                        <td><?php echo '<i class="fa fa-'.$icon.' text-'.$color.'"></i> '.$_SESSION["lista"]["caja"]["accion"]["tipo"][$value["tipo"]]["accion"]; ?></td>
                                                        <td><?php echo '<span class="text-'.$color.'">'.$operador.'$'.round($value["monto"], 2).'</span>' ?></td>
                                                        <td class="text-center"><?php echo ($value["procesado"] == 1 && !is_null($value["fechaModificacion"])) ? '<i class="fa fa-check-square-o text-success"></i>' : '<i class="fa fa-square-o text-info"></i>' ?></td>
                                                        <td class="d-flex justify-content-end">
                                                            <div class="btn-group"> 
                                                                <?php
                                                                    echo '<button type="button" onclick="$(\'#mov-data-'.$key.'\').toggleClass(\'d-none\')" id="detalle" class="btn btn-sm btn-outline-info"><i class="fa fa-expand"></i></button>';
                                                                    if(is_numeric($value["venta"]) && $value["venta"] > 0){ 
                                                                        echo '<button type="button" id="comprobante" onclick="facturaVisualizar('.$value["venta"].')" class="btn btn-sm btn-outline-info"><i class="fa fa-file-pdf-o"></i></button>';
                                                                    }
                                                                    if(($value["tipo"] != 6 && $value["tipo"] != 7) && $value["estado"] == 1 && is_numeric($value["venta"]) && $value["venta"] > 0 && date("Y-m-d", strtotime($value["fechaCarga"])) == date("Y-m-d", strtotime("-3 hour"))){
                                                                        echo '<button type="button" onclick="ventaAnularFormulario('.$value["venta"].')" id="anular" class="btn btn-sm btn-danger"><i class="fa fa-ban"></i></button>';
                                                                    }
                                                                ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td id="mov-<?php echo $key ?>-accion" style="display: none" colspan="4"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                    </tr>
                                                    <tr id="mov-data-<?php echo $key ?>" class="d-none" style="background-color: var(--sec-main)"> 
                                                        <td><?php echo "#".$key ?></td>
                                                        <td colspan="3"><?php echo nl2br($value["observacion"]) ?></td>
                                                        <td><?php echo date("d/m/Y", strtotime($value["fechaCarga"]))." ".date("H:i A", strtotime($value["fechaCarga"])) ?></td> 
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                    </tr>
                                                    <?php
                                                }
                                            }else{
                                                ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No se encontraron registros.</td>
                                                    <td class="d-none"></td>
                                                    <td class="d-none"></td>
                                                    <td class="d-none"></td>
                                                    <td class="d-none"></td>
                                                    <td class="d-none"></td>
                                                    <td class="d-none"></td>
                                                </tr>
                                                <?php
                                            }
                                        }else{
                                            ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Hubo un error al recibir la información. <b>Intente nuevamente.</b></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                            </tr>
                                            <?php
                                            Sistema::debug('error', 'caja.class.php - historial - Error en los datos recibidos de la caja.');
                                        } 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <script>
                        <?php
                            if(!$small){
                                ?> 
                                dataTableSet("#tabla-caja-historial", false, [[5, 10, 25, 50, 100, -1],[5, 10, 25, 50, 100, "Todos"]], 5, [ 1, "desc" ]);
                                <?php
                            }
                        ?>
                        tippy('#anular', {
                            content: 'Anular venta',
                            delay: [150,150],
                            animation: 'fade'
                        });
                        tippy('#detalle', {
                            content: 'Detalle de venta',
                            delay: [150,150],
                            animation: 'fade'
                        });
                        tippy('#comprobante', {
                            content: 'Comprobante de venta',
                            delay: [150,150],
                            animation: 'fade'
                        });
                    </script>
                    <?php
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Error al recibir la información de la caja. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - historial - Usuario no logueado.');
            }
        } 

        public static function getOperador($idCaja, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idCaja) && is_numeric($idCaja) && $idCaja > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_caja", "operador", "id = '".$idCaja."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return $dataQuery["operador"];
                        }else{
                            Sistema::debug('error', 'caja.class.php - getOperador - No se encontró información de la caja. Ref.: Caja: '.$idCaja.' - Error: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'caja.class.php - getOperador - Error al consultar la información de la caja. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - getOperador - Error en identificador de caja. Ref.: '.$idCaja);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - getOperador - Usuario no logueado.');
            }
            return false;
        }

        public static function corroboraAcceso($idCaja = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $data = $_SESSION["usuario"]->getCajaData();
                $cajaOperador = Caja::getOperador((is_numeric($idCaja) && $idCaja > 0) ? $idCaja : $data["actividadCaja"]);
                if(is_numeric($cajaOperador) && $cajaOperador > 0){
                    if(is_array($data) && count($data) == 4){
                        if(is_numeric($data["actividadCaja"]) 
                        && Compania::cajaCorroboraExistencia($data["actividadCaja"]) 
                        && !Caja::corroboraLibre($data["actividadCaja"]) 
                        && is_numeric($cajaOperador) && $cajaOperador > 0  && $_SESSION["usuario"]->getId() == $cajaOperador
                        && date("Y-m-d", strtotime($data["actividadFechaInicio"])) == date("Y-m-d", strtotime("-3 hour"))){
                            return true;
                        }else{
                            Sistema::debug('info', 'caja.class.php - corroboraAcceso - La configuración de actividad en caja del usuario es incorrecta. Ref.: '.count($data));
                        }
                    }else{
                        Sistema::debug('info', 'caja.class.php - corroboraAcceso - Usuario no tiene configurado actividad en caja.');
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - corroboraAcceso - Identificador de operador en caja incorrecto. Ref.: '.$cajaOperador);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - corroboraAcceso - Usuario no logueado.');
            }
            return false;
        }

        public static function actvidadJornadaGetData($idJornada, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idJornada) && is_numeric($idJornada) && $idJornada > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_caja_jornada", "*", "id = '".$idJornada."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        $data = [];
                        if(DataBase::getNumRows($query) == 1){
                            while($dataQuery = DataBase::getArray($query)){
                                $data[$dataQuery["id"]] = $dataQuery;
                            }
                            foreach($data AS $key => $value){
                                foreach($value AS $iKey => $iValue){
                                    if(is_int($iKey)){
                                        unset($data[$key][$iKey]);
                                    }
                                }
                                $fechaFin = (strlen($data[$idJornada]["fechaFin"]) > 0) ? $data[$idJornada]["fechaFin"] : date("Y-m-d H:i:s", strtotime("-3 hour"));
                                $data[$value["id"]]["movimientos"] = Caja::historialData($data[$idJornada]["caja"], $data[$idJornada]["fechaInicio"], $fechaFin, $data[$idJornada]["operador"], $data[$idJornada]["sucursal"], $data[$idJornada]["compañia"]);
                                $data[$value["id"]]["ventas"] = Venta::historialData($data[$idJornada]["caja"], $data[$idJornada]["fechaInicio"], $fechaFin, $data[$idJornada]["operador"], $data[$idJornada]["sucursal"], $data[$idJornada]["compañia"]);
                                $data[$value["id"]]["cobros"] = Venta::historialCobroData($data[$idJornada]["caja"], $data[$idJornada]["fechaInicio"], $fechaFin, $data[$idJornada]["operador"], $data[$idJornada]["sucursal"], $data[$idJornada]["compañia"]);
                                $data[$value["id"]]["stats"] = [
                                    "caja" => [
                                        "efectivo" => [
                                            "apertura" => 0,
                                            "cierre" => 0
                                        ],
                                        "horario" => [
                                            "apertura" => null,
                                            "cierre" => null
                                        ],
                                        "movimiento" => [
                                            "ingreso" => 0,
                                            "egreso" => 0,
                                            "detalle" => [
                                                "cobro" => [
                                                    "cantidad" => 0,
                                                    "volumen" => 0
                                                ],
                                                "deposito" => [
                                                    "cantidad" => 0,
                                                    "volumen" => 0
                                                ],
                                                "pago" => [
                                                    "cantidad" => 0,
                                                    "volumen" => 0
                                                ],
                                                "retiro" => [
                                                    "cantidad" => 0,
                                                    "volumen" => 0
                                                ],
                                                "venta" => [
                                                    "cantidad" => 0,
                                                    "volumen" => 0
                                                ],
                                            ], 
                                            "estado" => [
                                                "procesada" => 0,
                                                "no procesada" => 0
                                            ]
                                        ]
                                    ],
                                    "recaudacion" => [ 
                                        "efectivo" => 0,
                                        "debito" => 0,
                                        "credito" => 0,
                                        "otro" => 0
                                    ],
                                    "venta" => [ 
                                        "volumen" => 0,
                                        "estado" => [
                                            "caja" => [
                                                "procesada" => 0,
                                                "no procesada" => 0
                                            ],
                                            "anulada" => 0
                                        ],
                                        "pago" => [ 
                                            "efectivo" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ],
                                            "debito" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ],
                                            "credito" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ],
                                            "efectivo + debito" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ],
                                            "efectivo + credito" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ],
                                            "debito + credito" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ],
                                            "efectivo + debito + credito" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ],
                                            "a cuenta" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ]
                                        ],
                                        "producto" => [
                                            "volumen" => 0,
                                            "stock" => [
                                                "procesada" => 0,
                                                "no procesada" => 0
                                            ],
                                            "lista" => []
                                        ],
                                        "tipo" => [
                                            "pedido" => [
                                                "cantidad" => 0,
                                                "volumen" => 0,
                                                "anulado" => [
                                                    "cantidad" => 0,
                                                    "volumen" => 0
                                                ]
                                            ],
                                            "mine" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ],
                                            "compañia" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ],
                                            "varios" => [
                                                "cantidad" => 0,
                                                "volumen" => 0
                                            ]
                                        ]
                                    ],
                                    "cobro" => [
                                        "estado" => [
                                            "anulado" => 0,
                                            "cobrado" => 0
                                        ],
                                        "contado" => 0,
                                        "debito" => 0,
                                        "credito" => 0
                                    ]
                                ];
                            }
                        }else{
                            Sistema::debug('info', 'caja.class.php - actividadJornadaVisualizar - No se encontraron registros de la jornada N° '.$idJornada.'. Ref.: '.DataBase::getNumRows($query));
                        }
                        return $data;
                    }else{
                        Sistema::debug('error', 'caja.class.php - actividadJornadaVisualizar - Error al consultar información de jornada. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - actividadJornadaVisualizar - Identificador de jornada incorrecto. Ref.: '.$idJornada); 
                }
            }else{
                Sistema::debug('error', 'caja.class.php - actividadJornadaGetData - Usuario no logueado.');
            }
            return false;
        }

        public static function jornadaListaData($idJornada = null, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("compañia_sucursal_caja_jornada", "*", ((is_numeric($idJornada)) ? "id = '".$idJornada."' AND " : "")."sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "ORDER BY fechaInicio DESC LIMIT 200");
                if($query){
                    $data = [];
                    if(DataBase::getNumRows($query) > 0){
                        while($dataQuery = DataBase::getArray($query)){
                            $data[$dataQuery["id"]] = $dataQuery;
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
                    Sistema::debug('error', 'caja.class.php - jornadaListaData - Error al consultar la información. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('error', 'caja.class.php - jornadaListaData - Usuario no logueado.');
            }
            return false;
        }

        public static function jornadaFormulario($idJornada = null){
            if(Sistema::usuarioLogueado()){
                $data = Caja::jornadaListaData($idJornada);
                if(is_array($data)){
                    if(count($data) > 0){
                        Session::iniciar();
                        ?>
                        <div id="container-caja-actividad-jornada-formulario" class="mine-container">
                            <div class="d-flex justify-content-between"> 
                                <div class="titulo">Lista de jornadas de trabajo</div>
                                <button type="button" onclick="$('#container-caja-actividad-jornada-formulario').remove()" class="btn delete"><i class="fa fa-times"></i></button>
                            </div>
                            <div class="p-1">
                                <table id="tabla-jornada-trabajo" class="table table-hover w-100">
                                    <thead>
                                        <tr>
                                            <td>N°</td>
                                            <td>Operador</td>
                                            <td>Caja</td>
                                            <td>Sucursal</td>
                                            <td>Período</td>
                                            <td>Acción</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            foreach($data AS $key => $value){
                                                ?>
                                                <tr>
                                                    <td><?php echo $value["id"] ?></td>
                                                    <td><?php echo $_SESSION["lista"]["operador"][$value["operador"]]["nombre"] ?></td>
                                                    <td><?php echo $value["caja"] ?></td>
                                                    <td><?php echo $value["sucursal"] ?></td>
                                                    <td><?php echo date("d/m/Y, H:i:s A", strtotime($value["fechaInicio"]))." - ".((strlen($value["fechaFin"]) > 0 && $value["estado"] == 2) ? date("d/m/Y, H:i:s A", strtotime($value["fechaFin"])) : "EN CURSO") ?></td>
                                                    <td>
                                                        <button type="button" onclick="actividadJornadaVisualizar(<?php echo $value['id'] ?>)" class="btn btn-success"><i class="fa fa-list-alt"></i> Ver</button>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div> 
                        <script>
                            dataTableSet("#tabla-jornada-trabajo", false, [[5, 10, 25, 50, 100, -1],[5, 10, 25, 50, 100, "Todos"]], 10, [ 1, "desc" ]);
                        </script>
                        <?php
                    }else{
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'No se encontraron jornadas registradas.';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información de las jornadas. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - jornadaFormulario - Usuario no logueado.');
            }
        }

        public static function actividadJornadaVisualizar($idJornada, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idJornada) && is_numeric($idJornada) && $idJornada > 0){
                    $data = Caja::actvidadJornadaGetData($idJornada);
                    if(is_array($data)){
                        if(count($data) > 0){
                            Session::iniciar(); 
                            $dataCompañia = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["data"][$_SESSION["usuario"]->getCompañia()];
                            $compañiaCredito = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["credito"];
                            $operador = $_SESSION["lista"]["operador"];
                            foreach($data[$idJornada]["movimientos"] AS $key => $value){ 
                                if($value["estado"] != 1){

                                    continue;
                                }
                                $montoRedondeado = round($value["monto"], 2);
                                if($value["procesado"] == 1){
                                    $data[$idJornada]["stats"]["caja"]["movimiento"]["estado"]["procesada"]++;
                                }else{
                                    $data[$idJornada]["stats"]["caja"]["movimiento"]["estado"]["no procesada"]++;
                                }
                                switch($value["tipo"]){
                                    case 1:
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["ingreso"] += $montoRedondeado;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["cobro"]["cantidad"]++;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["cobro"]["volumen"] += $montoRedondeado;
                                        break;
                                    case 2:
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["ingreso"] += $montoRedondeado;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["deposito"]["cantidad"]++;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["deposito"]["volumen"] += $montoRedondeado;
                                        break;
                                    case 3:
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["egreso"] -= $montoRedondeado;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["pago"]["cantidad"]++;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["pago"]["volumen"] -= $montoRedondeado;
                                        break;
                                    case 4:
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["egreso"] -= $montoRedondeado;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["retiro"]["cantidad"]++;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["retiro"]["volumen"] -= $montoRedondeado;
                                        break;
                                    case 5:
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["ingreso"] += $montoRedondeado;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["venta"]["cantidad"]++;
                                        $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["venta"]["volumen"] += $montoRedondeado;
                                        break;
                                    case 6:
                                        $data[$idJornada]["stats"]["caja"]["efectivo"]["apertura"] = $montoRedondeado;
                                        break;
                                    
                                }
                            }
                            foreach($data[$idJornada]["ventas"] AS $key => $value){
                                if($value["estado"] != 1){ 
                                    if($value["pedido"] == 1){
                                        $data[$idJornada]["stats"]["venta"]["tipo"]["pedido"]["anulado"]["cantidad"]++;
                                        $data[$idJornada]["stats"]["venta"]["tipo"]["pedido"]["anulado"]["volumen"] += $value["total"];
                                    }else{
                                        $data[$idJornada]["stats"]["venta"]["estado"]["anulada"]++;
                                    }
                                    continue;
                                }
                                if($value["pedido"] == 1){
                                    $data[$idJornada]["stats"]["venta"]["tipo"]["pedido"]["cantidad"]++;
                                    $data[$idJornada]["stats"]["venta"]["tipo"]["pedido"]["volumen"] += $value["total"];
                                }
                                $data[$idJornada]["stats"]["venta"]["volumen"]++;
                                $contado = round($value["contado"], 2);
                                $debito = round($value["debito"], 2);
                                $credito = ($value["pago"] == 3 || $value["pago"] == 5 || $value["pago"] == 6 || $value["pago"] == 7) ? round(($value["credito"] * $compañiaCredito[$value["financiacion"]]["interes"]), 2) : $value["credito"];
                                $total = round($value["total"], 2);
                                $producto = explode(",", $value["producto"]);
                                $productoCantidad = explode(",", $value["productoCantidad"]);
                                $productoPrecio = explode(",", $value["productoPrecio"]);
                                if($value["pago"] == 1){
                                    if($value["procesadoCaja"] == 1){
                                        $data[$idJornada]["stats"]["venta"]["estado"]["caja"]["procesada"]++;
                                    }else{
                                        $data[$idJornada]["stats"]["venta"]["estado"]["caja"]["no procesada"]++;
                                    }
                                }
                                foreach(explode(",", $value["procesadoStock"]) AS $iKey => $iValue){ 
                                    if($iValue == 1){
                                        $data[$idJornada]["stats"]["venta"]["producto"]["stock"]["procesada"]++;
                                    }else{
                                        $data[$idJornada]["stats"]["venta"]["producto"]["stock"]["no procesada"]++;
                                    }
                                }
                                foreach($producto AS $jKey => $jValue){ 
                                    if($jValue[0] == "*"){
                                        if(str_replace("*", "", $jValue) == 0){
                                            $productoNombre = "Varios";
                                            $productoCodigo = "Sin código";
                                            $tipo = "varios";
                                        }else{
                                            $productoNombre = "Producto propio empresa."; //$_SESSION["lista"]["producto"]["noCodificado"][str_replace("*", "", $jValue)]["nombre"]; //fix nombre producto propio empresa
                                            $productoCodigo = "Codigo barra propio empresa.";//((strlen($_SESSION["lista"]["producto"]["noCodificado"][str_replace("*", "", $jValue)]["codigoBarra"]) > 0) ? "PFC-".$dataCompañia["id"]."-".$_SESSION["lista"]["producto"]["noCodificado"][str_replace("*", "", $jValue)]["codigoBarra"] : "Sin código"); 
                                            $tipo = "compañia";
                                        }
                                    }elseif($jValue > 0){
                                        $productoNombre = $_SESSION["lista"]["producto"]["codificado"][$jValue]["nombre"];
                                        $productoCodigo = $_SESSION["lista"]["producto"]["codificado"][$jValue]["codigoBarra"];
                                        $tipo = "mine";
                                    }
                                    $data[$idJornada]["stats"]["venta"]["tipo"][$tipo]["cantidad"] += $productoCantidad[$jKey];
                                    $data[$idJornada]["stats"]["venta"]["tipo"][$tipo]["volumen"] += $productoPrecio[$jKey] * $productoCantidad[$jKey];
                                    if(array_key_exists($jValue, $data[$idJornada]["stats"]["venta"]["producto"]["lista"]) && $data[$idJornada]["stats"]["venta"]["producto"]["lista"][$jValue]["precio"] == $productoPrecio[$jKey]){
                                        $data[$idJornada]["stats"]["venta"]["producto"]["lista"][$jValue]["cantidad"] += $productoCantidad[$jKey]; 
                                    }else{
                                        array_push($data[$idJornada]["stats"]["venta"]["producto"]["lista"], [
                                            "idProducto" => $jValue,
                                            "nombre" => $productoNombre,
                                            "codigo" => $productoCodigo,
                                            "cantidad" => $productoCantidad[$jKey],
                                            "precio" => $productoPrecio[$jKey]
                                        ]);
                                    }
                                    $data[$idJornada]["stats"]["venta"]["producto"]["volumen"] += $productoCantidad[$jKey];
                                }
                                switch($value["pago"]){
                                    case 1: //Contado efectivo
                                        $data[$idJornada]["stats"]["recaudacion"]["efectivo"] += $contado;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["efectivo"]["volumen"] += $contado;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["efectivo"]["cantidad"]++;
                                        break;
                                    case 2: //Débito
                                        $data[$idJornada]["stats"]["recaudacion"]["debito"] += $debito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["debito"]["volumen"] += $debito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["debito"]["cantidad"]++;
                                        break;
                                    case 3: //Crédito
                                        $data[$idJornada]["stats"]["recaudacion"]["credito"] += $credito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["credito"]["volumen"] += $credito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["credito"]["cantidad"]++;
                                        break;
                                    case 4: //Contado + débito
                                        $data[$idJornada]["stats"]["recaudacion"]["efectivo"] += $contado;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["efectivo"]["volumen"] += $contado;
                                        $data[$idJornada]["stats"]["recaudacion"]["debito"] += $debito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["debito"]["volumen"] += $debito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["efectivo + debito"]["cantidad"]++;
                                        break;
                                    case 5: //Contado + crédito
                                        $data[$idJornada]["stats"]["recaudacion"]["efectivo"] += $contado;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["efectivo"]["volumen"] += $contado;
                                        $data[$idJornada]["stats"]["recaudacion"]["credito"] += $credito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["credito"]["volumen"] += $credito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["efectivo + credito"]["cantidad"]++;
                                        break;
                                    case 6: //Débito + crédito
                                        $data[$idJornada]["stats"]["recaudacion"]["debito"] += $debito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["debito"]["volumen"] += $debito;
                                        $data[$idJornada]["stats"]["recaudacion"]["credito"] += $credito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["credito"]["volumen"] += $credito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["debito + credito"]["cantidad"]++;
                                        break;
                                    case 7: //Contado + débito + crédito
                                        $data[$idJornada]["stats"]["recaudacion"]["efectivo"] += $contado;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["efectivo"]["volumen"] += $contado;
                                        $data[$idJornada]["stats"]["recaudacion"]["debito"] += $debito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["debito"]["volumen"] += $debito;
                                        $data[$idJornada]["stats"]["recaudacion"]["credito"] += $credito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["credito"]["volumen"] += $credito;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["efectivo + debito + credito"]["cantidad"]++;
                                        break;
                                    case 8:
                                        $data[$idJornada]["stats"]["venta"]["pago"]["a cuenta"]["volumen"] += $contado;
                                        $data[$idJornada]["stats"]["venta"]["pago"]["a cuenta"]["cantidad"]++;
                                        break;
                                }
                            }
                            foreach($data[$idJornada]["cobros"] AS $key => $value){
                                if($value["estado"] != 1){
                                    $data[$idJornada]["stats"]["cobro"]["estado"]["anulado"]++;
                                    continue;
                                }
                                $contado = round($value["contado"], 2);
                                $debito = round($value["debito"], 2);
                                $credito = ($value["pago"] == 3 || $value["pago"] == 5 || $value["pago"] == 6 || $value["pago"] == 7) ? round(($value["credito"] * $compañiaCredito[$value["financiacion"]]["interes"]), 2) : $value["credito"];
                                $total = round($value["total"], 2);
                                $data[$idJornada]["stats"]["cobro"]["estado"]["cobrado"]++;
                                switch($value["pago"]){
                                    case 1: //Contado efectivo
                                        $data[$idJornada]["stats"]["cobro"]["contado"] += $contado;
                                        break;
                                    case 2: //Débito
                                        $data[$idJornada]["stats"]["cobro"]["debito"] += $debito;
                                        break;
                                    case 3: //Crédito
                                        $data[$idJornada]["stats"]["cobro"]["credito"] += $credito;
                                        break;
                                    case 4: //Contado + débito
                                        $data[$idJornada]["stats"]["cobro"]["contado"] += $contado;
                                        $data[$idJornada]["stats"]["cobro"]["debito"] += $debito;
                                        break;
                                    case 5: //Contado + crédito
                                        $data[$idJornada]["stats"]["cobro"]["contado"] += $contado;
                                        $data[$idJornada]["stats"]["cobro"]["credito"] += $credito;
                                        break;
                                    case 6: //Débito + crédito
                                        $data[$idJornada]["stats"]["cobro"]["debito"] += $debito;
                                        $data[$idJornada]["stats"]["cobro"]["credito"] += $credito;
                                        break;
                                    case 7: //Contado + débito + crédito
                                        $data[$idJornada]["stats"]["cobro"]["contado"] += $contado;
                                        $data[$idJornada]["stats"]["cobro"]["debito"] += $debito;
                                        $data[$idJornada]["stats"]["cobro"]["credito"] += $credito;
                                        break;
                                }
                            }
                            $totalEfectivo = ($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["venta"]["volumen"] + $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["retiro"]["volumen"] + $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["pago"]["volumen"] + $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["deposito"]["volumen"] + $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["cobro"]["volumen"]);
                            $totalContado = ($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["venta"]["volumen"]  + $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["deposito"]["volumen"] + $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["cobro"]["volumen"]);
                            ?>
                            <div class="d-flex justify-content-end">
                                <button type="button" onclick="actividadJornadaVisualizar(<?php echo $idJornada ?>)" class="btn btn-success d-none"><i class="fa fa-list-alt"></i> Ver</button>
                                <button type="button" onclick="printDiv()" class="btn btn-primary">Imprimir <i class="fa fa-print"></i></button>
                            </div>
                            <div id="comprobante" style="position: relative; padding: 1em; margin: 0.825em; background-color: var(--white); box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12) !important;">
                                <div style="padding: 10px; display: flex; justify-content: space-between; align-items: center">
                                    <div style="display: flex; flex-direction: column; justify-content: space-around;">
                                        <span style="font-size: 2em; font-weight-bold;"><?php echo $dataCompañia["nombre"] ?></span>
                                        <span><?php echo $dataCompañia["direccion"] ?></span>
                                        <span><?php echo $dataCompañia["telefono"] ?></span>
                                    </div>
                                    <img src="image/compañia/<?php echo $dataCompañia["id"] ?>/logo.png" style="height: 4em;" alt="<?php echo $dataCompañia["nombre"] ?>" />
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid lightgray; border-bottom: 1px solid lightgray;">
                                    <div style="display: flex; flex-direction: column; padding: 1.5em 0; font-size: 1.1em;">
                                        <span><strong><?php echo "Sucursal ".Compania::sucursalGetNombre($data[$idJornada]["sucursal"]) ?></strong></span>
                                        <span><b>Código Caja:</b> <?php echo $data[$idJornada]["caja"] ?></span>
                                        <span><b>Codigo Operador:</b> OFC-<?php echo $operador[$data[$idJornada]["operador"]]["id"] ?></span>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end">
                                        <span><b>Actividad N°: </b><?php echo $idJornada ?></span>
                                        <span><b>Inicio: </b><?php echo date("d/m/Y, H:i:s A", strtotime($data[$idJornada]["fechaInicio"])) ?></span>
                                        <span><b>Fin: </b><?php echo (strlen($data[$idJornada]["fechaFin"]) > 0) ? date("d/m/Y, H:i:s A", strtotime($data[$idJornada]["fechaFin"])) : date("d/m/Y, H:i:s A", strtotime("-3 hour")) ?></span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 0.375em">
                                    <span><b>Operador:</b> <?php echo $operador[$data[$idJornada]["operador"]]["nombre"] ?></span>
                                    <b>COMPROBANTE CIERRE DE CAJA</b>
                                </div>
                                <div>
                                    <table style="border-top: 1px solid lightgray; border-bottom: 1px solid lightgray; width: 100%;">
                                        <thead style="border-top: 1px solid lightgray; border-bottom: 1px solid lightgray;">
                                            <tr style="text-align: center; font-weight: bold; background-color: burlywood;">
                                                <td style="width: 100%; padding: 1.1em;">
                                                    Descripción
                                                </td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="width: 100%; border-bottom: 1px solid black; padding: 2em 0.5em;">
                                                    <b>CAJA INICIO:</b> <span>$ <?php echo number_format($data[$idJornada]["stats"]["caja"]["efectivo"]["apertura"], 2, ",", ".") ?></span><br>
                                                    <div style="display: none">
                                                        <b>Ingresos:</b> $ <?php echo $data[$idJornada]["stats"]["caja"]["movimiento"]["ingreso"] ?><br>
                                                        <b>Egresos:</b> $ <?php echo $data[$idJornada]["stats"]["caja"]["movimiento"]["egreso"] ?>
                                                    </div>
                                                    <div style="display: flex; justify-content: center; padding: 1em 0 1em 1em; border-top: 7.5px solid darkgray">
                                                        <b style="font-variant-caps: all-petite-caps; font-size: 1.2em;">Detalle</b>
                                                    </div> 
                                                    <div style="margin-left: 0.5em; display: flex; flex-direction: column;">
                                                        <div class="<?php echo ($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["cobro"]["volumen"] == 0) ? "d-none" : "" ?>">
                                                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                                <b>Cobros en contado:</b>
                                                            </div>
                                                            <div style="display: flex; flex-direction: column;">
                                                                <div style="display: flex; flex-direction: column; padding: .25em 1.5em;">
                                                                    <?php
                                                                        $count = 0;
                                                                        foreach($data[$idJornada]["movimientos"] AS $key => $value){
                                                                            if($value["tipo"] == 1){
                                                                                $count++;
                                                                                echo '
                                                                                    <div style="display: flex; justify-content: space-between">
                                                                                        <span style="font-size: 11.5px; padding-right: 2em;">'.$value["observacion"].'</span>
                                                                                        <span style="font-size: 11.5px; min-width: max-content;">$ '.number_format($value["monto"], 2, ",", ".").'</span>
                                                                                    </div>
                                                                                    <div style="display: none; justify-content: space-between">
                                                                                        <span>'.$operador[$value["operador"]]["nombre"].'</span>
                                                                                        <span>'.date("d/m/Y, H:i A", strtotime($value["fechaCarga"])).'</span>
                                                                                    </div> 
                                                                                ';
                                                                            }
                                                                        }
                                                                            
                                                                        if($count == 0){
                                                                            echo '
                                                                                <div style="display: flex; justify-content: center">
                                                                                    <span style="font-size: 11.5px;">Sin movimientos.</span>
                                                                                </div>
                                                                            ';
                                                                        }
                                                                    ?>
                                                                </div> 
                                                                <div style="display: flex; justify-content: end; border-top: 1px solid black; font-weight: bold;">
                                                                    <span style="font-weight: bold; padding-right: 1em">Total Cobros:</span>
                                                                    <span>$ <?php echo number_format($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["cobro"]["volumen"], 2, ",", ".") ?></span> 
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="<?php echo ($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["deposito"]["volumen"] == 0) ? "d-none" : "" ?>">
                                                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                                <b>Depositos en efectivo:</b>
                                                            </div>
                                                            <div style="display: flex; flex-direction: column;">
                                                                <div style="display: flex; flex-direction: column; padding: .25em 1.5em;">
                                                                    <?php
                                                                        $count = 0;
                                                                        foreach($data[$idJornada]["movimientos"] AS $key => $value){
                                                                            if($value["tipo"] == 2){
                                                                                $count++;
                                                                                echo '
                                                                                    <div style="display: flex; justify-content: space-between">
                                                                                        <span style="font-size: 11.5px; padding-right: 2em;">'.$value["observacion"].'</span>
                                                                                        <span style="font-size: 11.5px; min-width: max-content;">$ '.number_format($value["monto"], 2, ",", ".").'</span>
                                                                                    </div>
                                                                                    <div style="display: none; justify-content: space-between">
                                                                                        <span>'.$operador[$value["operador"]]["nombre"].'</span>
                                                                                        <span>'.date("d/m/Y, H:i A", strtotime($value["fechaCarga"])).'</span>
                                                                                    </div> 
                                                                                ';
                                                                            }
                                                                        }
                                                                            
                                                                        if($count == 0){
                                                                            echo '
                                                                                <div style="display: flex; justify-content: center">
                                                                                    <span style="font-size: 11.5px;">Sin movimientos.</span>
                                                                                </div>
                                                                            ';
                                                                        }
                                                                    ?>
                                                                </div>
                                                                <div style="display: flex; justify-content: end; border-top: 1px solid black; font-weight: bold;">
                                                                    <span style="font-weight: bold; padding-right: 1em">Total Depósitos:</span>
                                                                    <span>$ <?php echo number_format($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["deposito"]["volumen"], 2, ",", ".") ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="<?php echo ($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["pago"]["volumen"] == 0) ? "d-none" : "" ?>">
                                                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                                <b>Pagos:</b>
                                                            </div>
                                                            <div style="display: flex; flex-direction: column;">
                                                                <div style="display: flex; flex-direction: column; padding: .25em 1.5em;">
                                                                    <?php
                                                                        $count = 0;
                                                                        foreach($data[$idJornada]["movimientos"] AS $key => $value){
                                                                            if($value["tipo"] == 3){
                                                                                $count++;
                                                                                echo '
                                                                                    <div style="display: flex; justify-content: space-between">
                                                                                        <span style="font-size: 11.5px;">'.$value["observacion"].'</span>
                                                                                        <span style="font-size: 11.5px;">$ -'.number_format($value["monto"], 2, ",", ".").'</span>
                                                                                    </div>
                                                                                    <div style="display: none; justify-content: space-between">
                                                                                        <span>'.$operador[$value["operador"]]["nombre"].'</span>
                                                                                        <span>'.date("d/m/Y, H:i A", strtotime($value["fechaCarga"])).'</span>
                                                                                    </div> 
                                                                                ';
                                                                            }
                                                                        }
                                                                            
                                                                        if($count == 0){
                                                                            echo '
                                                                                <div style="display: flex; justify-content: center">
                                                                                    <span style="font-size: 11.5px;">Sin movimientos.</span>
                                                                                </div>
                                                                            ';
                                                                        }
                                                                    ?>
                                                                </div> 
                                                                <div style="display: flex; justify-content: end; border-top: 1px solid black; font-weight: bold;">
                                                                    <span style="font-weight: bold; padding-right: 1em">Total Pagos:</span>
                                                                    <span>$ <?php echo number_format($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["pago"]["volumen"], 2, ",", ".") ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="<?php echo ($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["retiro"]["volumen"] == 0) ? "d-none" : "" ?>">
                                                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                                <b>Retiro:</b>
                                                            </div>
                                                            <div style="display: flex; flex-direction: column;">
                                                                <div style="display: flex; flex-direction: column; padding: .25em 1.5em;">
                                                                    <?php
                                                                        $count = 0;
                                                                        foreach($data[$idJornada]["movimientos"] AS $key => $value){
                                                                            if($value["tipo"] == 4){
                                                                                $count++;
                                                                                echo '
                                                                                    <div style="display: flex; justify-content: space-between">
                                                                                        <span style="font-size: 11.5px;">'.$value["observacion"].'</span>
                                                                                        <span style="font-size: 11.5px;">$ -'.number_format($value["monto"], 2, ",", ".").'</span>
                                                                                    </div>
                                                                                    <div style="display: none; justify-content: space-between">
                                                                                        <span>'.$operador[$value["operador"]]["nombre"].'</span>
                                                                                        <span>'.date("d/m/Y, H:i A", strtotime($value["fechaCarga"])).'</span>
                                                                                    </div> 
                                                                                ';
                                                                            }
                                                                        } 
                                                                        if($count == 0){
                                                                            echo '
                                                                                <div style="display: flex; justify-content: center">
                                                                                    <span style="font-size: 11.5px;">Sin movimientos.</span>
                                                                                </div>
                                                                            ';
                                                                        }
                                                                    ?>
                                                                </div>
                                                                <div style="display: flex; justify-content: end; border-top: 1px solid black; font-weight: bold;">
                                                                    <span style="font-weight: bold; padding-right: 1em">Total Retiros:</span>
                                                                    <span>$ <?php echo number_format($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["retiro"]["volumen"], 2, ",", ".") ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black"> 
                                                            <b>Ventas en contado:</b> 
                                                        </div>
                                                        <div style="display: flex; flex-direction: column;">
                                                            <div style="display: flex; flex-direction: column; padding: .25em 1.5em;"> 
                                                                <div style="display: flex; justify-content: space-between">
                                                                    <span style="font-size: 11.5px;">Total</span>
                                                                    <span style="font-size: 11.5px;"><?php echo $data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["venta"]["cantidad"] ?></span>
                                                                </div>
                                                            </div>
                                                            <div style="display: flex; justify-content: end; border-top: 1px solid black; font-weight: bold;">
                                                                <span style="font-weight: bold; padding-right: 1em">Total Ventas:</span>
                                                                <span>$ <?php echo number_format($data[$idJornada]["stats"]["caja"]["movimiento"]["detalle"]["venta"]["volumen"], 2, ",", ".") ?></span>
                                                            </div> 
                                                        </div>
                                                        <div style="display: flex; justify-content: end; padding: 1em 0 1em 1em; font-size: 1.15em; font-weight: bold;">
                                                            <b style="padding-right: 1em">Total Efectivo:</b> <span>$ <?php echo number_format($totalEfectivo, 2, ",", ".") ?></span>
                                                        </div>
                                                    </div>

                                                    <div style="display: flex; justify-content: center; padding: 1em 0 1em 1em; border-top: 7.5px solid darkgray;">
                                                        <b style="font-variant-caps: all-petite-caps; font-size: 1.2em;">Recaudación</b>
                                                    </div>
                                                    <div style="margin-left: 0.5em; display: flex; flex-direction: column">
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; margin-bottom: 0.35em">
                                                            <span>Ventas en Contado:</span> <span style="font-weight: bold;">$ <?php echo number_format($totalContado, 2, ",", ".") ?></span>
                                                        </div>
                                                        <div style="margin-bottom: 0.35em">
                                                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; align-items: center;">
                                                                <span>Ventas en Débito:</span> <span style="margin-right: 1em; font-size: 11.5px;">$ <?php echo number_format($data[$idJornada]["stats"]["recaudacion"]["debito"], 2, ",", ".") ?></span>
                                                            </div>
                                                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid black; align-items: center;">
                                                                <span>Ventas en Crédito:</span> <span style="margin-right: 1em; font-size: 11.5px;">$ <?php echo number_format($data[$idJornada]["stats"]["recaudacion"]["credito"], 2, ",", ".") ?></span>
                                                            </div>
                                                            <div style="display: flex; justify-content: end; font-weight: bold;">
                                                                <span style="padding-right: 1em">Total ventas débito + crédito:</span> <span style="">$ <?php echo number_format(($data[$idJornada]["stats"]["recaudacion"]["debito"] + $data[$idJornada]["stats"]["recaudacion"]["credito"]), 2, ",", ".") ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="<?php echo ($data[$idJornada]["stats"]["cobro"]["debito"] == 0 && $data[$idJornada]["stats"]["cobro"]["credito"] == 0) ? "d-none" : "" ?>" style="margin-bottom: 0.35em"> 
                                                            <div style="display: <?php echo ($data[$idJornada]["stats"]["cobro"]["debito"] == 0) ? "none" : "flex" ?>; justify-content: space-between; border-bottom: 1px dashed black;">
                                                                <span>Cobros en Débito:</span> <span style="margin-right: 1em; font-size: 11.5px;">$ <?php echo number_format($data[$idJornada]["stats"]["cobro"]["debito"], 2, ",", ".") ?></span>
                                                            </div>
                                                            <div style="display: <?php echo ($data[$idJornada]["stats"]["cobro"]["credito"] == 0) ? "none" : "flex" ?>; justify-content: space-between; border-bottom: 1px solid black;">
                                                                <span>Cobros en Crédito:</span> <span style="margin-right: 1em; font-size: 11.5px;">$ <?php echo number_format($data[$idJornada]["stats"]["cobro"]["credito"], 2, ",", ".") ?></span>
                                                            </div>
                                                            <div style="display: flex; justify-content: end; font-weight: bold;">
                                                                <span style="padding-right: 1em">Total cobros débito + crédito:</span> <span style="">$ <?php echo number_format(($data[$idJornada]["stats"]["cobro"]["debito"] + $data[$idJornada]["stats"]["cobro"]["credito"]), 2, ",", ".") ?></span>
                                                            </div>
                                                        </div>
                                                        <div style="display: flex; justify-content: end; padding: 1em 0 1em 1em; font-size: 1.15em; font-weight: bold;">
                                                            <span style="padding-right: 1em">Gran Total:</span> $ <?php echo number_format(($totalContado + $data[$idJornada]["stats"]["recaudacion"]["debito"] + $data[$idJornada]["stats"]["recaudacion"]["credito"] + $data[$idJornada]["stats"]["cobro"]["debito"] + $data[$idJornada]["stats"]["cobro"]["credito"]), 2, ",", ".") ?>
                                                        </div>
                                                    </div>

                                                    <div style="display: flex; justify-content: center; padding: 1em 0 1em 1em; border-top: 7.5px solid darkgray;">
                                                        <b style="font-variant-caps: all-petite-caps; font-size: 1.2em;">Control</b>
                                                    </div>
                                                    <div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; margin-bottom: 0.35em">
                                                            <span>VENTAS TOTALES:</span> <span><?php echo $data[$idJornada]["stats"]["venta"]["volumen"] ?></span>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; margin-bottom: 0.35em">
                                                            <span>VENTAS A CUENTA (<?php echo $data[$idJornada]["stats"]["venta"]["pago"]["a cuenta"]["cantidad"] ?>):</span> <span>$ <?php echo number_format($data[$idJornada]["stats"]["venta"]["pago"]["a cuenta"]["volumen"], 2, ",", ".")  ?></span>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; margin-bottom: 0.35em">
                                                            <span>VENTAS ANULADAS:</span> <span><?php echo $data[$idJornada]["stats"]["venta"]["estado"]["anulada"] ?></span>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; margin-bottom: 1.35em">
                                                            <span>CANT. PRODUCTOS VENDIDOS:</span> <span><?php echo $data[$idJornada]["stats"]["venta"]["producto"]["volumen"] ?></span>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; margin-bottom: 0.35em;">
                                                            <span>PEDIDOS (<?php echo $data[$idJornada]["stats"]["venta"]["tipo"]["pedido"]["cantidad"] ?>)</span> <span>$ <?php echo number_format($data[$idJornada]["stats"]["venta"]["tipo"]["pedido"]["volumen"], 2, ",", ".")?></span>
                                                        </div>
                                                        <div style="margin-bottom: 1.35em;">
                                                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; margin-bottom: 0.35em;">
                                                                <span>PEDIDOS ANULADOS (<?php echo $data[$idJornada]["stats"]["venta"]["tipo"]["pedido"]["anulado"]["cantidad"] ?>)</span>
                                                            </div>
                                                            <div style="display: flex; ">
                                                                <?php
                                                                    if($data[$idJornada]["stats"]["venta"]["tipo"]["pedido"]["anulado"]["cantidad"] > 0){
                                                                        foreach($data[$idJornada]["ventas"] AS $key => $value){
                                                                            if($value["pedido"] == 1 && $value["estado"] != 1){
                                                                                ?>
                                                                                <div style="padding: 0 .5em .5em .5em; width: 100%; font-size: 11.5px;">
                                                                                    <div style="display: flex">
                                                                                        <div style="display: flex; flex-direction: start; margin-right: auto;">
                                                                                            <span><?php echo mb_strtoupper(Cliente::getNombre($value["cliente"])) ?></span>
                                                                                            <span style="margin-left: .5em;">Obs.: <?php echo (strlen($value["anuladoObservacion"]) > 0) ? $value["anuladoObservacion"] : "(Sistema) Sin observación." ?></span>
                                                                                        </div>
                                                                                        <span>$ <?php echo number_format($value["total"], 2, ",", ".") ?></span>
                                                                                    </div>
                                                                                </div>
                                                                                <?php
                                                                            }
                                                                        }
                                                                    }
                                                                ?>  
                                                            </div>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; margin-bottom: 0.35em">
                                                            <span>MOV. VERIFICADOS:</span> <span><?php echo $data[$idJornada]["stats"]["caja"]["movimiento"]["estado"]["procesada"] ?></span>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black; margin-bottom: 0.35em">
                                                            <span>MOV. NO VERIFICADOS:</span> <span><?php echo $data[$idJornada]["stats"]["caja"]["movimiento"]["estado"]["no procesada"] ?></span>
                                                        </div>
                                                    </div>
                                                    <div style="margin-left: 0.5em; margin-bottom: 0em;">
                                                        <?php
                                                            if(false){
                                                                foreach($data[$idJornada]["stats"]["venta"]["producto"]["lista"] AS $key => $value){
                                                                    echo '<div style="margin-bottom: 0.5em; border-bottom: 1px dashed black; display: flex; flex-direction: column;">
                                                                        <div style="display: flex; justify-content: space-between">
                                                                            <span>'.mb_strtoupper($value["nombre"]).'</span>
                                                                            <span></span>
                                                                        </div>
                                                                        <div style="display: flex; justify-content: space-between">
                                                                            <span>['.$value["codigo"].'] '.$value["cantidad"].' X $ '.$value["precio"].'</span>
                                                                            <span>$ '.round(($value["precio"] * $value["cantidad"]), 2).'</span>
                                                                        </div>
                                                                    </div>';
                                                                }
                                                            }
                                                        ?>
                                                    </div>
                                                    <div style="display: none; flex-direction: column"> 
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                            <u>MiNe:</u> <span>Cant. <?php echo $data[$idJornada]["stats"]["venta"]["tipo"]["mine"]["cantidad"].": $ ".$data[$idJornada]["stats"]["venta"]["tipo"]["mine"]["volumen"] ?></span>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                            <u>Compañía:</u> <span>Cant. <?php echo $data[$idJornada]["stats"]["venta"]["tipo"]["compañia"]["cantidad"].": $ ".$data[$idJornada]["stats"]["venta"]["tipo"]["compañia"]["volumen"] ?></span>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                            <u>Varios:</u> <span>Cant. <?php echo $data[$idJornada]["stats"]["venta"]["tipo"]["varios"]["cantidad"].": $ ".$data[$idJornada]["stats"]["venta"]["tipo"]["varios"]["volumen"] ?></span>
                                                        </div> 
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr style="display: none">
                                                <td style="width: 100%; border-bottom: 1px solid black; padding: 2em 0.5em;">
                                                    <b>VENTAS:</b>
                                                    <br><br>
                                                    <u>Total:</u> <?php echo $data[$idJornada]["stats"]["venta"]["volumen"] ?><br>
                                                    <div style="margin-left: 0.5em; display: flex; flex-direction: column">
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                            <u>Efectivo:</u> <span>Cant. <?php echo $data[$idJornada]["stats"]["venta"]["pago"]["efectivo"]["cantidad"].": $ ".$data[$idJornada]["stats"]["venta"]["pago"]["efectivo"]["volumen"] ?></span>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                            <u>Débito:</u> <span>Cant. <?php echo $data[$idJornada]["stats"]["venta"]["pago"]["debito"]["cantidad"].": $ ".$data[$idJornada]["stats"]["venta"]["pago"]["debito"]["volumen"] ?></span>
                                                        </div>
                                                        <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed black">
                                                            <u>Crédito:</u> <span>Cant. <?php echo $data[$idJornada]["stats"]["venta"]["pago"]["credito"]["cantidad"].": $ ".$data[$idJornada]["stats"]["venta"]["pago"]["credito"]["volumen"] ?></span>
                                                        </div>
                                                        <div style="display: none; justify-content: space-between; border-bottom: 1px dashed black">
                                                            <u>Otro:</u> <span>Cant. <?php echo $data[$idJornada]["stats"]["venta"]["pago"]["otro"]["cantidad"].": $ ".$data[$idJornada]["stats"]["venta"]["pago"]["otro"]["volumen"] ?></span>
                                                        </div> 
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold; text-align: center; width: 100%; padding: 1em; border-bottom: 1px solid black;">
                                                    EFECE Soluciones Informáticas 
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot style="border-top: 1px solid lightgray; border-bottom: 1px solid lightgray; "> 
                                            <tr>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <script> 
                                function printDiv(){
                                    var divToPrint=document.getElementById('comprobante');
                                    var newWin=window.open('','Print-Window');
                                    newWin.document.open();
                                    newWin.document.write('<html><style>body{ font-family: arial; font-size: 10.5px; }</style><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');
                                    newWin.document.close();
                                    setTimeout(function(){newWin.close();},10);

                                }
                                $("#print").click(function(){
                                    w = window.open();
                                    w.document.write($('#bonosUniq').html());
                                    w.print();
                                    w.close();
                                })
                            
                            </script>
                            <?php
                        }else{
                            $mensaje['tipo'] = 'info';
                            $mensaje['cuerpo'] = 'No se encontraron datos de la jornada. <b>Intente nuevamente o contacte al administrador.</b>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al consultar la información de la jornada. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - actividadJornadaVisualizar - Error en identificador de jornada. Ref.: '.$idJornada);
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Error al recibir la información de la jornada. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - actividadJornadaVisualizar - Usuario no logueado.');
            }
        }

        public static function jornadaRegistrar($idCaja, $fechaInicio, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idCaja) && is_numeric($idCaja) && $idCaja > 0){
                    if(Compania::cajaCorroboraExistencia($idCaja)){
                        Session::iniciar();
                        $query = DataBase::insert("compañia_sucursal_caja_jornada", "caja,operador,sucursal,compañia,fechaInicio", "'".$idCaja."','".$_SESSION["usuario"]->getId()."','".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."','".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."','".$fechaInicio."'");
                        if($query){
                            $_SESSION["usuario"]->updateActividadJornada(DataBase::getLastId());
                            return true;
                        }else{
                            Sistema::debug('error', 'caja.class.php - actividadCerrar - Error al registrar jornada. Ref.: '.DataBase::getError());
                        }
                    }else{
                        Sistema::debug('error', 'caja.class.php - jornadaRegistrar - La caja no pertenece a la compañía. Ref.: '.$idCaja);
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - jornadaRegistrar - Error en identificador de caja. Ref.: '.$idCaja);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - jornadaRegistrar - Usuario no logueado.');
            }
            return false;
        }

        public static function jornadaCerrar($idCaja, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idCaja) && is_numeric($idCaja) && $idCaja > 0){
                    if(Compania::cajaCorroboraExistencia($idCaja)){
                        Session::iniciar();
                        $query = DataBase::update("compañia_sucursal_caja_jornada", "estado = 2", "caja = '".$idCaja."' AND fechaFin IS NULL AND operador = '".$_SESSION["usuario"]->getId()."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'");
                        if($query){
                            return true;
                        }else{
                            Sistema::debug('error', 'caja.class.php - actividadCerrar - Error al registrar jornada. Ref.: '.DataBase::getError());
                        }
                    }else{
                        Sistema::debug('error', 'caja.class.php - jornadaRegistrar - La caja no pertenece a la compañía. Ref.: '.$idCaja);
                    }
                }else{
                    Sistema::debug('error', 'caja.class.php - jornadaRegistrar - Error en identificador de caja. Ref.: '.$idCaja);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - jornadaRegistrar - Usuario no logueado.');
            }
            return false;
        }

        public static function actividadCerrar($sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $data = $_SESSION["usuario"]->getCajaData();
                if(is_array($data) && count($data) == 4){
                    if(Caja::corroboraAcceso()){
                        if(Caja::jornadaCerrar($data["actividadCaja"])){
                            if($_SESSION["usuario"]->actividadCajaLimpiar()){
                                if(Caja::bloquear($data["actividadCaja"], 0)){
                                    $time = 1750;
                                    $mensaje['tipo'] = 'success';
                                    $mensaje['cuerpo'] = 'Caja cerrada satisfactoriamente. <b>Redireccionando...</b>';
                                    if(!Sistema::cajaSetMonto($data["actividadCaja"], 400, null, 1, $_SESSION["usuario"]->getSucursal(), $_SESSION["usuario"]->getCompañia())){
                                        $mensaje['cuerpo'] .= '<div class="p-2 text-center">Hubo un error al resetear el monto de caja por sistema. <b>Informe al administrador a la brevedad: Caja cod. '.$data["actividadCaja"].'</b></div>';
                                        $time = 7500;
                                    }
                                    Alert::mensaje($mensaje);
                                    echo '<script>setTimeout(() => { actividadJornadaVisualizar('.$data["actividadJornada"].') }, '.$time.')</script>';
                                }else{
                                    $mensaje['tipo'] = 'danger';
                                    $mensaje['cuerpo'] = 'Hubo un error al desbloquear la caja. <b>Intente nuevamente, si el problema persiste contacte al administrador.</b>';
                                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="cajaActividadCerrar()" class="btn btn-danger">Intentar nuevamente</button></div>';
                                    Alert::mensaje($mensaje);
                                }
                            }else{
                                $mensaje['tipo'] = 'danger';
                                $mensaje['cuerpo'] = 'Hubo un error al limpiar las actividades en caja del usuario. <b>Intente nuevamente, si el problema persiste contacte al administrador.</b>';
                                $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="cajaActividadCerrar()" class="btn btn-danger">Intentar nuevamente</button></div>';
                                Alert::mensaje($mensaje);
                            }
                        }else{
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al cerrar la jornada. <b>Intente nuevamente, si el problema persiste contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="cajaActividadCerrar()" class="btn btn-danger">Intentar nuevamente</button></div>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'No se pudo comprobar el acceso a la caja para realizar esta acción. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    } 
                }else{
                    Sistema::debug('error', 'caja.class.php - actividadCerrar - Error en arreglo de datos de usuario. Ref.: '.count($data));
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información de trabajo. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'caja.class.php - actividadCerrar - Usuario no logueado.');
            }
            return false;
        }

        public static function gestion($idCaja = null, $actividad = null){
            if(Sistema::usuarioLogueado()){ 
                Session::iniciar();
                if(Caja::corroboraAcceso()){
                    $idCaja = $_SESSION["usuario"]->getActividadCaja();
                    $actividad = 1;
                }
                if(is_numeric($idCaja) && $idCaja > 0 && Compania::cajaCorroboraExistencia($idCaja)){
                    ?>
                    <div class="mine-container">
                        <div class="d-flex justify-content-between">
                            <div class="titulo"><?php echo mb_strtoupper($_SESSION["usuario"]->getInfo(null, "sucursal")) ?> - Gestión de caja</div>
                            <?php
                                if($actividad === 1){
                                    ?>
                                    <div class="btn-group">
                                        <button type="button" onclick="ventaRegistrarFormulario('#container-caja-accion', true)" class="btn btn-outline-info"><i class="fa fa-plus"></i> Venta</button>
                                        <button type="button" onclick="cajaAccionRegistrarFormulario(null, true)" class="btn btn-outline-info"><i class="fa fa-level-down"></i> Movimiento</button>
                                        <button type="button" onclick="cajaActividadCerrar('#container-caja-accion')" class="btn btn-outline-info"><i class="fa fa-file-text"></i> Cierre</button>
                                    </div>
                                    <?php
                                }
                            ?>
                        </div>
                        <div class="d-flex">
                            <div class="w-25 d-flex flex-column justify-content-start">
                                <div class="d-flex justify-content-start p-4 mine-container sm w-100" style="height: min-content;">
                                    <i class="fa fa-2x fa-mouse-pointer text-secondary mr-2 p-3"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-muted h5 font-weight-bold mb-2 text-secondary">Balance Caja</span>
                                        <span class="h4 font-weight-bold">$ <span id="caja-monto"><?php echo number_format(Caja::dataGetMonto($idCaja), 2, ",", "."); ?></span></span>
                                    </div>
                                </div>
                                <canvas id="venta-chart-1"></canvas>
                            </div>
                            <div class="w-75" id="container-caja-accion">
                                <span class="text-muted w-100 text-center">Aguardando tarea a realizar...</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between"> 
                            <div class="w-50" id="container-caja-historial">
                                <?php Caja::historial($idCaja, $actividad, true) ?>
                            </div>
                            <div class="w-50" id="container-ventas-historial">
                                <?php Venta::historial($idCaja, $actividad, true) ?>
                            </div>
                        </div>
                    </div>
                    <script>
                        $(document).ready(() => {
                            return;
                            let ventas = <?php echo Sistema::json_format(Venta::historialData()) ?>;
                            console.log(ventas);
                            let labels = ["Contado", "Débito", "Crédito", "Contado + Débito", "Contado + Crédito", "Débito + Crédito", "Efectivo + Débito + Crédito"];
                            let datasets = [{
                                label: "# de ventas",
                                data: [8, 4, 12],
                                backgroundColor: ['rgba(255, 99, 132, 0.8)',
                                    'rgba(54, 162, 235, 0.8)',
                                    'rgba(255, 206, 86, 0.8)'
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)'
                                ],
                                borderWidth: 1
                            }];
                            charter("venta-chart-1", 4);
                        })
                    </script>
                    <?php
                }else{
                    Sistema::controlActividadCaja();
                }
            }else{
                Sistema::debug('error', 'caja.class - gestion - Usuario no logueado.');
            }
        }
    }
?>