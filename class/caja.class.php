<?php
    class Caja{
        public static function dataGetMonto($sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("compañia_sucursal_caja", "monto", "sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
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

        public static function update($monto, $accionId, $operador = null, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($monto) && is_numeric($monto) && $monto > 0 && isset($accionId) && is_numeric($accionId) && $accionId > 0){
                    Session::iniciar();
                    $getCajaAccionTipo = Caja::historialGetAccionTipo($accionId);
                    $cajaAccionTipo = Lista::cajaAccionTipo();
                    if(!is_bool($getCajaAccionTipo) && is_numeric($getCajaAccionTipo)){
                        foreach($cajaAccionTipo AS $key => $value){
                            if($getCajaAccionTipo == $value["id"]){
                                switch($value["actividad"]){
                                    case 1:
                                        $query = DataBase::update("compañia_sucursal_caja", "monto = (monto + '".$monto."'), accion = '".$accionId."', operador = '".((is_numeric($operador)) ? $operador : $_SESSION["usuario"]->getId())."'", "sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'"); 
                                    break 1;
                                    case 2:
                                        $cajaMonto = Caja::dataGetMonto($sucursal, $compañia);
                                        if($cajaMonto >= $monto){
                                            $query = DataBase::update("compañia_sucursal_caja", "monto = (monto - '".$monto."'), accion = '".$accionId."', operador = '".((is_numeric($operador)) ? $operador : $_SESSION["usuario"]->getId())."'", "sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'"); 
                                        }else{
                                            Sistema::debug('error', 'caja.class.php - update - No tiene fondos suficientes. Ref.: $'.$cajaMonto);
                                            return false;
                                        }
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

        public static function accionRegistrar($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    $cajaAccionTipo = Lista::cajaAccionTipo();
                    foreach($cajaAccionTipo AS $key => $value){
                        if($data["tipo"] == $value["id"]){
                            if($value["actividad"] == 2){ 
                                $cajaMonto = Caja::dataGetMonto($_SESSION["usuario"]->getSucursal(), $_SESSION["usuario"]->getCompañia());
                                if($cajaMonto < $data["monto"]){
                                    $mensaje['tipo'] = 'info';
                                    $mensaje['cuerpo'] = 'No tiene fondos suficientes para realizar el movimiento.';
                                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-info">Regresar</button></div>';
                                    Alert::mensaje($mensaje);
                                    exit;
                                }
                            }
                            $query = DataBase::insert("compañia_sucursal_caja_historial", "tipo,observacion,monto,venta,operador,sucursal,compañia", "'".$data["tipo"]."','".$data["observacion"]."','".$data["monto"]."',".((isset($data["venta"]) && is_numeric($data["venta"]) && $data["venta"] > 0) ? "'".$data["venta"]."'" : "NULL").",'".$_SESSION["usuario"]->getId()."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."'"); 
                            break;
                        }
                    }
                    if($query){
                        $mensaje['tipo'] = 'success';
                        $mensaje['cuerpo'] = 'Se registró el movimiento satisfactoriamente.';
                        Alert::mensaje($mensaje);
                        $data["operador"] = $_SESSION["usuario"]->getId();
                        $data["sucursal"] = $_SESSION["usuario"]->getSucursal();
                        $data["compañia"] = $_SESSION["usuario"]->getCompañia();
                        $data["identificador"] = DataBase::getLastId();
                        Sistema::compañiaSucursalCajaUpdate($data);
                        echo '<script>cajaUpdateMonto('.Caja::dataGetMonto().')</script>';
                        echo '<script>cajaHistorial()</script>';
                        return true;
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al registrar el movimiento en caja. <b>Intente nuevamente o contacte al administrador.</b>';
                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                        Alert::mensaje($mensaje);
                        Sistema::debug('error', 'caja.class.php - accionRegistrar - Error al registrar la información en base de datos. Ref.: '.DataBase::getError());
                        return false;
                    }
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'No se reconoce el movimiento a realizar. <b>Intente nuevamente o contacte administrador.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('alert', 'caja.class.php - accionRegistrar - No se reconoce el movimiento. Ref.: '.$data["tipo"]);
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con la información recibida. <b>Intente nuevamente o contacte al administrador.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('error', 'caja.class.php - accionRegistrar - Error en el arreglo de datos.');
                }
            }else{
                Sistema::debug('error', 'caja.class.php - accionRegistrar - Usuario no logueado.');
            }
        }

        public static function accionRegistrarFormulario(){
            if(Sistema::usuarioLogueado()){
                $accion = $_SESSION["lista"]["caja"]["accion"]["tipo"];
                ?>
                <div id="container-caja-accion-formulario" class="mine-container">
                    <div class="d-flex justify-content-between"> 
                        <div class="titulo">Acción en caja</div>
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
                            <button type="button" onclick="cajaAccionRegistrar()" class="btn btn-success">Registrar</button>
                        </div>
                    </form>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'caja.class.php - accionFormulario - Usuario no logueado.');
            }
        }

        public static function historialData($sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("compañia_sucursal_caja_historial", "*", "sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "ORDER BY fechaCarga DESC LIMIT 250");
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
                Sistema::debug('error', 'caja.class.php - historialData - Usuario no logueado.');
            }
            return false;
        }

        public static function historial(){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $data = Caja::historialData();
                ?>
                <div class="mine-container">
                    <div class="titulo">Historial de caja</div>
                    <div class="p-1">
                        <table id="tabla-caja-historial" class="table table-hover table-responsive w-100">
                            <thead>
                                <tr>
                                    <td scope="col">N°</td>
                                    <td>Fecha</td>
                                    <td>Tipo</td>
                                    <td class="w-100">Descripción</td>
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
                                                ?>
                                                <tr>
                                                    <td><?php echo "#".$key ?></td>
                                                    <td><?php echo date("d/m/Y", strtotime($value["fechaCarga"]))." ".date("H:i A", strtotime($value["fechaCarga"])) ?></td>
                                                    <td><?php echo ($_SESSION["lista"]["caja"]["accion"]["tipo"][$value["tipo"]]["actividad"] == 1) ? '<i class="fa fa-plus text-success"></i>' : '<i class="fa fa-minus text-danger"></i>' ?></td>
                                                    <td><?php echo nl2br($value["observacion"]) ?></td>
                                                    <td><?php echo (($_SESSION["lista"]["caja"]["accion"]["tipo"][$value["tipo"]]["actividad"] == 1) ? '<span class="text-success">+$'.round($value["monto"], 2).'</span>' : '<span class="text-danger">-$'.round($value["monto"], 2).'</span>') ?></td>
                                                    <td><?php echo ($value["procesado"] == 1 && !is_null($value["fechaModificacion"])) ? '<i class="fa fa-check-square-o text-success"></i>' : '<i class="fa fa-square-o text-info"></i>' ?></td>
                                                    <td class="btn-group text-right">
                                                        <button type="button" class="btn btn-sm btn-outline-info"><i class="fa fa-expand"></i></button>
                                                        <?php
                                                            if(is_numeric($value["venta"]) && $value["venta"] > 0){
                                                                echo '<button type="button" onclick="facturaVisualizar('.$value["venta"].')" class="btn btn-sm btn-outline-info"><i class="fa fa-file-pdf-o"></i></button>';
                                                            }
                                                        ?>
                                                    </td>
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
                    dataTableSet("#tabla-caja-historial", true, [[5, 10, 25, 50, 100, -1],[5, 10, 25, 50, 100, "Todos"]], 10, [ 1, "desc" ]);
                </script>
                <?php
            }else{
                Sistema::debug('error', 'caja.class.php - historial - Usuario no logueado.');
            }
        }

        public static function gestion(){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                ?>
                <div class="mine-container">
                    <div class="d-flex justify-content-between">
                        <div class="titulo"><?php echo mb_strtoupper($_SESSION["usuario"]->getInfo(null, "sucursal")) ?> - Gestión de caja</div>
                        <div class="btn-group">
                            <button type="button" onclick="cajaAccionRegistrarFormulario()" class="btn btn-outline-info"><i class="fa fa-plus"></i> movimiento</button>
                            <button type="button" onclick="ventaRegistrarFormulario('#container-caja-accion')" class="btn btn-outline-info"><i class="fa fa-plus"></i> venta</button>
                        </div>
                    </div>
                    <div style="font-weight: bold; font-size: 2em; color: var(--mono-green-1);">
                        Monto en caja $ <span id="caja-monto"><?php echo Caja::dataGetMonto(); ?></span>
                    </div>
                    <div id="container-caja-accion"></div>
                    <div id="container-caja-historial" class="p-1">
                        <?php Caja::historial() ?>
                    </div>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'caja.class - gestion - Usuario no logueado.');
            }
        }
    }
?>