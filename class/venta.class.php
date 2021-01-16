<?php
    class Venta {
        public static function cajaHistorialDataGetMonto($idVenta, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idVenta) && is_numeric($idVenta) && $idVenta > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_caja_historial", "monto", "venta = '".$idVenta."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return $dataQuery["monto"];
                        }else{
                            Sistema::debug('info', 'venta.class.php - cajaHistorialDataGetMonto - No se encontró información del historial. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'venta.class.php - cajaHistorialDataGetMonto - Error al consultar la información del historial. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error' , 'venta.class.php - cajaHistorialDataGetMonto - Identificador de venta incorrecto. Ref.: '.$idVenta);
                }
            }else{
                Sistema::debug('error', 'venta.class.php - cajaHistorialDataGetMonto - Usuario no logueado.');
            }
            return false;
        }

        public static function dataGetCaja($idVenta, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idVenta) && is_numeric($idVenta) && $idVenta > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_venta", "caja", "id = '".$idVenta."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return $dataQuery["caja"];
                        }else{
                            Sistema::debug('info', 'venta.class.php - dataGetCaja - No se encontró información de la venta. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'venta.class.php - dataGetCaja - Error al consultar la información de la venta. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error' , 'venta.class.php - dataGetCaja - Identificador de venta incorrecto. Ref.: '.$idVenta);
                }
            }else{
                Sistema::debug('error', 'venta.class.php - dataGetCaja - Usuario no logueado.');
            }
            return false;
        }

        public static function dataGetPago($idVenta, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idVenta) && is_numeric($idVenta) && $idVenta > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_venta", "pago", "id = '".$idVenta."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return $dataQuery["pago"];
                        }else{
                            Sistema::debug('info', 'venta.class.php - dataGetPago - No se encontró información de la venta. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'venta.class.php - dataGetPago - Error al consultar la información de la venta. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error' , 'venta.class.php - dataGetPago - Identificador de venta incorrecto. Ref.: '.$idVenta);
                }
            }else{
                Sistema::debug('error', 'venta.class.php - dataGetPago - Usuario no logueado.');
            }
            return false;
        }

        public static function historialData($idCaja = null, $fechaInicio = null, $fechaFin = null, $operador = null, $sucursal = null, $compañia = null, $limit = 250){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("compañia_sucursal_venta", "*", ((!is_null($idCaja) && strlen($idCaja) > 0) ? "caja = '".$idCaja."' AND " : "").((!is_null($fechaInicio) && !is_null($fechaFin)) ? "fechaCarga BETWEEN '".$fechaInicio."' AND '".$fechaFin."' AND " : "").((!is_null($operador)) ? "operador = '".$operador."' AND " : "")."sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "ORDER BY fechaCarga DESC LIMIT ".$limit);
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
                    Sistema::debug('error', 'venta.class.php - historialData - Error al consultar la información del historial. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('error', 'venta.class.php - historialData - Usuario no logueado.');
            }
            return false;
        }

        public static function anularFormulario($idVenta){
            if(Sistema::usuarioLogueado()){ 
                ?>
                <div class="curtain" id="container-venta-anular-formulario">
                    <div class="mine-container w-50 h-50">
                        <div class="d-flex justify-content-between"> 
                            <div class="titulo">Anular venta N° <?php echo $idVenta ?></div>
                            <button type="button" onclick="$('#container-venta-anular-formulario').remove()" class="btn delete"><i class="fa fa-times"></i></button>
                        </div>
                        <?php
                            if(isset($idVenta) && is_numeric($idVenta) && $idVenta > 0){
                                Session::iniciar();
                                ?>
                                <div id="venta-anular-process" style="display: none"></div>
                                <form id="venta-anular-form" action="./engine/venta/anular.php" form="#venta-anular-form" process="#venta-anular-process">
                                    <div class="form-group">
                                        <label for="motivo">Motivo:</label>
                                        <select class="form-control" id="motivo" name="motivo">
                                            <option value=""> - Seleccionar una opción - </option>
                                            <?php
                                                if(is_array($_SESSION["lista"]["anulacion"])){
                                                    if(count($_SESSION["lista"]["anulacion"]) > 0){
                                                        foreach($_SESSION["lista"]["anulacion"] AS $key => $value){
                                                            echo '<option value="'.$value["id"].'">'.$value["tipo"].'</option>';
                                                        }
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="observacion">Observaciones:</label>
                                        <textarea class="form-control" id="observacion" name="observacion" rows="3"></textarea>
                                    </div>
                                    <div class="form-group d-none">
                                        <label class="col-form-label" for="idVenta">Identificador:</label>
                                        <input type="text" class="form-control" value="<?php echo $idVenta ?>" id="idVenta" name="idVenta" readonly>
                                    </div>
                                    <button type="button" class="btn btn-success" onclick="ventaAnular(<?php echo $idVenta ?>)">Registrar</button>
                                </form>
                                <?php
                            }else{
                                $mensaje['tipo'] = 'danger';
                                $mensaje['cuerpo'] = 'Hubo un error con la información recibida. <b>Intente nuevamente o contacte al administrador.</b>';
                                Alert::mensaje($mensaje);
                            }
                        ?>
                    </div>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'venta.class.php - anularFormulario - Usuario no logueado.');
            }
        }

        public static function anular($data, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    $idVenta = $data["idVenta"]; 
                    Session::iniciar();
                    $pago = Venta::dataGetPago($idVenta);
                    $caja = Venta::dataGetCaja($idVenta);
                    if(is_numeric($pago) && array_key_exists($pago, $_SESSION["lista"]["pago"]) && is_numeric($caja) && Compania::cajaCorroboraExistencia($caja)){
                        $query = DataBase::update("compañia_sucursal_venta", "estado = 0, anuladoMotivo = '".$data["motivo"]."', anuladoObservacion = ".((!is_null($data["observacion"]) && strlen($data["observacion"]) > 0) ? "'".$data["observacion"]."'" : "NULL").", anuladoOperador = '".$_SESSION["usuario"]->getId()."'", "id = '".$idVenta."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'");
                        if($query){
                            if($pago == 1 || $pago == 4 || $pago == 5 || $pago == 7){
                                $query = DataBase::update("compañia_sucursal_caja_historial", "estado = 0, observacion = CONCAT('[ANULADA] ', observacion)", "venta = '".$idVenta."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'");
                                if($query){
                                    $monto = Venta::cajaHistorialDataGetMonto($idVenta);
                                    if(is_numeric($monto) && $monto >= 0){
                                        $query = DataBase::update("compañia_sucursal_caja", "monto = monto - '".$monto."'", "id = '".$caja."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'");
                                        if(!$query){
                                            $mensaje['tipo'] = 'danger';
                                            $mensaje['cuerpo'] = 'Hubo un error al descontar el monto [$'.$monto.'] de la caja. Realícelo manualmente con un movimiento de corrección en caja o contacte al administrador.';
                                            Alert::mensaje($mensaje);
                                        }
                                    }else{
                                        $mensaje['tipo'] = 'danger';
                                        $mensaje['cuerpo'] = 'Hubo un error al descontar el monto [$'.$monto.'] de la caja. Realícelo manualmente con un movimiento de corrección en caja o contacte al administrador.';
                                        Alert::mensaje($mensaje);
                                    }
                                }else{
                                    $mensaje['tipo'] = 'danger';
                                    $mensaje['cuerpo'] = 'Hubo un error al anular el movimiento en caja. <b>Contacte al administrador a la brevedad.</b>';
                                    Alert::mensaje($mensaje);
                                }
                            }
                            echo '<script>setTimeout(() => { cajaRefreshUI('.Caja::dataGetMonto($caja).', '.$caja.') }, 250)</script>';
                            $mensaje['tipo'] = 'success';
                            $mensaje['cuerpo'] = 'Venta anulada satisfactoriamente!';
                            Alert::mensaje($mensaje);
                        }else{
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al anular la venta. <b>Intente nuevamente o contacte al administrador.</b>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        $mensaje['tipo'] = 'warning';
                        $mensaje['cuerpo'] = 'No se pudo comprobar la información de la venta. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al realizar el movimiento, el identificador de venta es incorrecto. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'venta.class.php - anular - Usuario no logueado.');
            }
        }

        public static function historial($idCaja = null, $actividad = null, $small = false){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $small = ($small === true || $small == "true" || $small == 1) ? true : false;
                $data = Venta::historialData($idCaja, null, null, null, null, null, ($small === true) ? 5 : 250);
                $pago = $_SESSION["lista"]["pago"];
                $productoBase = $_SESSION["lista"]["producto"];
                ?>
                <div class="mine-container <?php echo ($small === true) ? "sm" : "" ?>">
                    <div class="titulo">Historial de ventas</div>
                    <div class="p-1">
                        <table id="tabla-venta-historial" class="table table-hover table-responsive w-100">
                            <thead>
                                <tr>
                                    <td class="text-center">Caja</td>
                                    <td>Tipo</td>
                                    <td>Monto</td>
                                    <td class="text-right" style="width: fit-content">Acciones</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(is_array($data)){
                                        if(count($data) > 0){
                                            foreach($data AS $key => $value){
                                                ?>
                                                <tr id="venta-<?php echo $key ?>" class="<?php echo ($value["estado"] != 1) ? "anulado" : "" ?>">
                                                    <td class="text-center"><?php echo "#".$value["caja"] ?></td>
                                                    <td class="w-100"><?php echo $pago[$value["pago"]]["pago"] ?></td>
                                                    <td><?php echo "$".round($value["total"], 2); ?></td>
                                                    <td class="btn-group text-right">
                                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="$('#venta-<?php echo $key ?>-productos').toggleClass('d-none')" title="Expandir detalle de artículos."><i class="fa fa-expand"></i></button>
                                                        <?php
                                                            if(is_numeric($value["nComprobante"]) && $value["nComprobante"] > 0){
                                                                echo '<button type="button" onclick="facturaVisualizar('.$value["id"].')" class="btn btn-sm btn-outline-info"><i class="fa fa-file-pdf-o"></i></button>';
                                                            }
                                                            if($value["estado"] == 1 && date("Y-m-d", strtotime($value["fechaCarga"])) == date("Y-m-d")){
                                                                echo '<button type="button" onclick="ventaAnularFormulario('.$value['id'].')" id="anular" class="btn btn-sm btn-danger"><i class="fa fa-ban"></i></button>';
                                                            }
                                                        ?>
                                                        
                                                    </td>
                                                </tr>
                                                <tr id="venta-<?php echo $key ?>-productos" class="d-none" style="background-color: var(--sec-main)">
                                                    <td colspan="4" class="text-center">
                                                        <?php
                                                            $producto = explode(",", $value["producto"]);
                                                            $productoCantidad = explode(",", $value["productoCantidad"]);
                                                            $productoPrecio = explode(",", $value["productoPrecio"]);

                                                            foreach($producto AS $iKey => $iValue){
                                                                $tipo = "codificado";
                                                                if($iValue[0] == "*"){
                                                                    $tipo = "noCodificado";
                                                                    $iValue = str_replace("*", "", $iValue);
                                                                }
                                                                ?>
                                                                <div class="row p-1">
                                                                    <div class="col-md-7 text-right"><?php echo ($iValue == 0) ? "VARIOS" : $productoBase[$tipo][$iValue]["nombre"] ?></div>
                                                                    <div class="col-md-2 text-right"><?php echo $productoCantidad[$iKey]." X "?></div>
                                                                    <div class="col-md-1 text-left"><?php echo " $".$productoPrecio[$iKey] ?></div>
                                                                    <div class="col-md-2"><?php echo "$".round(($productoPrecio[$iKey] * $productoCantidad[$iKey]), 2); ?></div>
                                                                </div>
                                                                <?php
                                                            }
                                                        ?>
                                                        <div class="d-flex justify-content-between p-1 mt-2 font-weight-bold"> 
                                                            <span><?php echo "Comprobante N° ".$value["nComprobante"] ?></span>
                                                            <span><?php echo "Fecha y hora: ".date("d/m/Y, H:i A", strtotime($value["fechaCarga"])); ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="d-none"></td>
                                                    <td class="d-none"></td>
                                                    <td class="d-none"></td>
                                                </tr>
                                                <?php
                                            }
                                        }else{
                                            ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No se encontraron registros.</td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                            </tr>
                                            <?php
                                        }
                                    }else{
                                        ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Hubo un error al recibir la información. <b>Intente nuevamente.</b></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                        </tr>
                                        <?php
                                        Sistema::debug('error', 'venta.class.php - historial - Error en los datos recibidos de la caja.');
                                    } 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                    if(<?php echo $small ?> === false){
                        dataTableSet("#tabla-venta-historial", false, [[10, 20, 50, 100, 200, -1],[5, 10, 25, 50, 100, "Todos"]], 20, [ 1, "desc" ]);
                    }
                </script>
                <?php
            }else{
                Sistema::debug('error', 'venta.class.php - historial - Usuario no logueado.');
            }
        }

        public static function registrar($data){
            if(Sistema::usuarioLogueado()){
                //echo '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>'; 
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    if(Caja::corroboraAcceso($data["idCaja"])){
                        if(isset($data["producto-identificador"]) && is_array($data["producto-identificador"]) && count($data["producto-identificador"]) > 0){
                            Session::iniciar();
                            Compania::reloadStaticData();
                            $credito = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["credito"];
                            $dataProducto = [];
                            $dataCaja = [];
                            $dataCaja["pago"] = $data["pago"];
                            $dataCaja["contado"] = (isset($data["monto-contado"])) ? $data["monto-contado"] : NULL;
                            $dataCaja["debito"] = (isset($data["monto-debito"])) ? $data["monto-debito"] : NULL;
                            $dataCaja["credito"] = (isset($data["monto-credito"])) ? $data["monto-credito"] : NULL;
                            $dataCaja["efectivo"] = (isset($data["monto-efectivo"])) ? $data["monto-efectivo"] : NULL;
                            $dataCaja["financiacion"] = (isset($data["cuota"])) ? $data["cuota"] : NULL;
                            $dataCaja["subtotal"] = 0;
                            $dataCaja["iva"] = (isset($data["iva"])) ? true : false;
                            $dataCaja["cliente"] = (isset($data["cliente"])) ? $data["cliente"] : null;
                            $dataCaja["descuento"] = $data["descuento"]; 
                            $dataCaja["producto"] = "";
                            $dataCaja["productoCantidad"] = "";
                            $dataCaja["productoPrecio"] = "";
                            foreach($data["producto-identificador"] AS $key => $value){ 
                                $dataProducto[$key]["id"] = ($value == 0) ? null : $_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value][($data["producto-tipo"][$key] == "codificado") ? "producto" : "productoNC"];
                                $dataProducto[$key]["idStock"] = ($value == 0) ? null : $value;
                                $dataProducto[$key]["stock"] = ($value == 0) ? null : $_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["stock"];
                                $dataProducto[$key]["precio"] = trim($data["producto-precio-unitario"][$key]); 
                                $dataProducto[$key]["cantidad"] = trim($data["producto-cantidad"][$key]);
                                $dataProducto[$key]["tipo"] = $data["producto-tipo"][$key];
                                $dataProducto[$key]["nombre"] = ($value == 0) ? $data["producto-descripcion"][$key] : $_SESSION["lista"]["producto"][$dataProducto[$key]["tipo"]][$dataProducto[$key]["id"]]["nombre"];
                                $dataProducto[$key]["subtotal"] = $dataProducto[$key]["cantidad"] * $dataProducto[$key]["precio"];
                                $dataCaja["subtotal"] += $dataProducto[$key]["subtotal"];
                                if($value != 0){
                                    if($dataProducto[$key]["precio"] != $_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["precio"] && $dataProducto[$key]["precio"] != $_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["precioMayorista"] && $dataProducto[$key]["precio"] != $_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["precioKiosco"]){
                                        $mensaje['tipo'] = 'warning';
                                        $mensaje['cuerpo'] = 'El precio del producto '.$dataProducto[$key]["nombre"].' no coincide con los registrados en stock. Corrobore los datos antes de continuar...';
                                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                                        Alert::mensaje($mensaje);
                                        echo $dataProducto[$key]["precio"]."<br>pmi = ".$_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["precio"]."<br>pma = ".$_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["precioMayorista"]."<br>pk = ".$_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["precioKiosco"];
                                        exit;
                                    }
                                } 
                                if(strlen($dataCaja["producto"]) > 0){
                                    $dataCaja["producto"] .= ",";
                                }
                                if(strlen($dataCaja["productoCantidad"]) > 0){
                                    $dataCaja["productoCantidad"] .= ",";
                                }
                                if(strlen($dataCaja["productoPrecio"]) > 0){
                                    $dataCaja["productoPrecio"] .= ",";
                                }
                                $dataCaja["producto"] .= (($data["producto-tipo"][$key] == "codificado") ? "" : "*").$value;
                                $dataCaja["productoPrecio"] .= $dataProducto[$key]["precio"];
                                $dataCaja["productoCantidad"] .= $data["producto-cantidad"][$key];
                            }
    
                            foreach($dataProducto AS $key => $value){
                                if($value["id"] != null && $value["idStock"] != null && $value["stock"] != null){
                                    if($value["stock"] < $value["cantidad"]){
                                        $mensaje['tipo'] = 'warning';
                                        $mensaje['cuerpo'] = 'El producto '.$value["nombre"].' no tiene stock disponible para venta. Stock disponible: '.$value["stock"].' - Cantidad solicitada: '.$value["cantidad"];
                                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                                        Alert::mensaje($mensaje);
                                        exit;
                                    }
                                }
                            }
    
                            if(!$dataCaja["iva"]){
                                $dataCaja["subtotal"] = $dataCaja["subtotal"] - ($dataCaja["subtotal"] / 100 * 21);
                            }

                            if($dataCaja["pago"] == 3 || $dataCaja["pago"] == 5 || $dataCaja["pago"] == 6){
                                $totalCredito = $dataCaja["credito"] * $credito[$dataCaja["financiacion"]]["interes"];
                            }else{
                                $totalCredito = 0;
                            }
    
                            $dataCaja["total"] = round($dataCaja["contado"] + $dataCaja["debito"] + $totalCredito - ($dataCaja["subtotal"] / 100 * $dataCaja["descuento"]), 2); 
    
                            $nComprobante = Compania::facturaIdUltima();
                            
                            if(is_numeric($nComprobante) && $nComprobante >= 0){
                                $query = DataBase::insert("compañia_sucursal_venta", "caja,nComprobante,producto,productoCantidad,productoPrecio,pago,contado,debito,credito,efectivo,financiacion,descuento,iva,cliente,subtotal,total,operador,sucursal,compañia", "'".$data["idCaja"]."','".($nComprobante + 1)."','".$dataCaja["producto"]."','".$dataCaja["productoCantidad"]."','".$dataCaja["productoPrecio"]."','".$dataCaja["pago"]."',".((strlen($dataCaja["contado"]) > 0) ? "'".$dataCaja["contado"]."'" : "NULL").",".((strlen($dataCaja["debito"]) > 0) ? "'".$dataCaja["debito"]."'" : "NULL").",".((strlen($dataCaja["credito"]) > 0) ? "'".$dataCaja["credito"]."'" : "NULL").",".((strlen($dataCaja["contado"]) > 0) ? "'".$dataCaja["efectivo"]."'" : "NULL").",".((is_numeric($dataCaja["financiacion"])) ? "'".$dataCaja["financiacion"]."'" : "NULL" ).",'".$dataCaja["descuento"]."','".(($dataCaja["iva"]) ? 1 : 0)."',".((isset($dataCaja["cliente"]) && is_numeric($dataCaja["cliente"]) && $dataCaja["cliente"] > 0) ? $dataCaja["cliente"] : "NULL").",'".$dataCaja["subtotal"]."','".$dataCaja["total"]."','".$_SESSION["usuario"]->getId()."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."'");
                                if($query){
                                    $idVenta = DataBase::getLastId();
                                    $stockRestar = Compania::stockRestar($dataCaja["producto"], $dataCaja["productoCantidad"]); 
                                    $productoStockRestar = "";
                                    foreach($stockRestar AS $key => $value){
                                        if(strlen($productoStockRestar) > 0){ $productoStockRestar .= ","; }
                                        $productoStockRestar .= (is_bool($value["status"]) && $value["status"]) ? 1 : 0; 
                                    }
                                    if($stockRestar){
                                        $query = DataBase::update("compañia_sucursal_venta", "procesadoStock = '".$productoStockRestar."'", "id = '".$idVenta."' AND sucursal = '".$_SESSION["usuario"]->getSucursal()."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                                        if(!$query){
                                            Sistema::debug('error', 'venta.class.php - registrar - Error al procesar stock. Ref.: '.$idVenta);
                                        }
                                    }else{
                                        Sistema::debug('error', 'venta.class.php - registar - Error al registrar movimiento en stock.');
                                    }
                                    if($dataCaja["pago"] == 1 || $dataCaja["pago"] == 4 || $dataCaja["pago"] == 5){
                                        $cajaInsertData = [
                                            "idCaja" => $data["idCaja"],
                                            "tipo" => 5,
                                            "observacion" => "Venta condición ".$_SESSION["lista"]["pago"][$dataCaja["pago"]]["pago"],
                                            "monto" => ($dataCaja["pago"] == 1) ? $dataCaja["total"] : $dataCaja["contado"],
                                            "venta" => $idVenta
                                        ];
                                        $cajaUpdate = Caja::accionRegistrar($cajaInsertData, false);
                                        if($cajaUpdate){
                                            $query = DataBase::update("compañia_sucursal_venta", "procesadoCaja = 1", "id = '".$idVenta."' AND sucursal = '".$_SESSION["usuario"]->getSucursal()."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                                            if(!$query){
                                                Sistema::debug('error', 'venta.class.php - registrar - Error al procesar venta. Ref.: '.$idVenta);
                                            }
                                        }else{
                                            Sistema::debug('error', 'venta.class.php - registar - Error al registrar acción en caja.');
                                        }
                                    }
                                    Compania::facturaVisualizar($idVenta);
                                    echo '<script>setTimeout(() => { cajaRefreshUI('.Caja::dataGetMonto($data["idCaja"]).', '.$data["idCaja"].') }, 250)</script>';
                                }else{
                                    $mensaje['tipo'] = 'danger';
                                    $mensaje['cuerpo'] = 'Hubo un error al registrar la venta. <b>Intente nuevamente o contacte al administrador.</b>';
                                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                                    Alert::mensaje($mensaje);
                                    Sistema::debug('error', 'venta.class.php - registrar - Error al registrar venta. Ref.: '.DataBase::getError());
                                }
                            }else{
                                $mensaje['tipo'] = 'warning';
                                $mensaje['cuerpo'] = 'Hubo un error al obtener el identificador del comprobante. <b>Intente nuevamente o contacte al administrador.</b>';
                                $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                                Alert::mensaje($mensaje);
                                Sistema::debug('alert', 'venta.class.php - registrar - Identificador de comprobante incorrecto. Ref.: '.$nComprobante);
                            }
                        }else{
                            $mensaje['tipo'] = 'info';
                            $mensaje['cuerpo'] = 'No se encontraron productos para registrar.';
                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-info">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                            Sistema::debug('info', 'venta.class.php - registrar - Cantidad de productos incorrecta. Ref.: '.count($data["producto-identificador"]));
                        }
                    }else{
                        $mensaje['tipo'] = 'warning';
                        $mensaje['cuerpo'] = 'Hubo un error al comprobar la caja de trabajo. <b>Intente nuevamente o contacte al administrador.</b>'; 
                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con la información recibida. <b>Intente nuevamente o contacte al administrador.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('error', 'venta.class.php - registrar - Error en el arreglo de datos recibidos.');
                }
            }else{
                Sistema::debug('error', 'venta.class.php - registrar - Usuario no logueado.');
            }
        }

        public static function registrarFormulario($small = false){ 
            if(Sistema::usuarioLogueado()){
                Sistema::controlActividadCaja();
                if(Caja::corroboraAcceso()){
                    Session::iniciar();
                    $idCaja = $_SESSION["usuario"]->getActividadCaja();
                    $dataCliente = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["cliente"];
                    $baseProductos = $_SESSION["lista"]["producto"];
                    $dataStock = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"];
                    $credito = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["credito"];
                    ?>
                    <div id="container-venta-formulario" class="mine-container <?php echo ($small === "true") ? "sm" : "" ?>">
                        <div class="d-flex justify-content-between">
                            <div class="titulo">Registrar nueva venta Caja N° <?php echo $idCaja ?></div>
                            <button type="button" onclick="$('#container-venta-formulario').remove();" class="btn delete"><i class="fa fa-times"></i></button>
                        </div>
                        <script> 
                            $(document).ready(function(){
                                $("#producto").on("keyup", 
                                    function(){
                                        return;
                                        $("#container-producto-lista").find("li").css({display: "none"});
                                        $("#buscador-vacio").addClass("d-none");
                                        var value = $(this).val().toLowerCase();
                                        if(value.length > 0){ 
                                            $("#container-producto-lista li").filter(function ()
                                            {
                                                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                                            });
                                            let resultados = $('#container-producto-lista').find('li:visible');
                                            if(resultados.length > 0){
                                                $("#buscador-vacio").addClass("d-none");
                                            }else{
                                                $("#buscador-vacio").removeClass("d-none");
                                            }
                                        }else{
                                            $("#container-producto-lista").find("li").css({display: "none"});
                                        }
                                    }
                                );
                            }); 
                        </script>
                        <div id="venta-registro-process" style="display: none;"></div>
                        <div id="venta-stepper-1" class="bs-stepper">
                            <div class="bs-stepper-header" role="tablist">
                                <div class="step" data-target="#venta-p-1">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="venta-p-1" id="venta-p-1-trigger">
                                        <span class="bs-stepper-circle">1</span>
                                        <span class="bs-stepper-label">Productos</span>
                                    </button>
                                </div>
                                <div class="line"></div>
                                <div class="step" data-target="#venta-p-2">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="venta-p-2" id="venta-p-2-trigger">
                                        <span class="bs-stepper-circle">2</span>
                                        <span class="bs-stepper-label">Modo de pago</span>
                                    </button>
                                </div>
                            </div>
                            <div class="bs-stepper-content">
                                <form id="venta-registro-form" action="./engine/venta/registrar.php" onsubmit="return null;" form="#venta-registro-form" process="#venta-registro-process">
                                    <div id="venta-p-1" class="content" role="tabpanel" aria-labelledby="venta-p-1-trigger">
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" onchange="ventaRegistrarFormularioUpdatetipoProducto()" id="tipoProducto" checked>
                                                <label class="custom-control-label" for="tipoProducto"><i class="fa fa-plus"></i> Agregar <span id="tipoProductoLabel">producto codificado</span></label>
                                            </div>
                                        </div>
                                        <div id="container-producto" class="form-group">
                                            <label for="producto" class="d-none"> producto</label>
                                            <input type="text" class="form-control" placeholder="Buscar producto" id="producto" autocomplete="off">
                                            <ul id="container-producto-lista" class="list-group" style="max-height: 15vh; overflow: auto;">
                                                <?php
                                                    if(is_array($dataStock) && count($dataStock) > 0){
                                                        foreach($dataStock AS $key => $value){
                                                            if((is_numeric($value["producto"]) && $value["producto"] > 0)){
                                                                $idProducto =  $value["producto"];
                                                                $tipo = "codificado";
                                                            }else{
                                                                $idProducto =  $value["productoNC"];
                                                                $tipo = "noCodificado";
                                                            }
                                                            ?>
                                                            <li class="list-group-item" id="c-p-c-b-<?php echo $baseProductos[$tipo][$idProducto]["codigoBarra"] ?>" style="display: block"  data-id-producto="<?php echo $value["id"] ?>" data-producto="<?php echo $baseProductos[$tipo][$idProducto]["nombre"] ?>" data-producto-tipo="<?php echo $tipo ?>" data-stock="<?php echo $value["stock"] ?>" data-precio="<?php echo $value["precio"] ?>" data-precio-mayorista="<?php echo $value["precioMayorista"] ?>" data-precio-kiosco="<?php echo $value["precioKiosco"] ?>" data-bar-code="<?php echo (is_numeric($baseProductos[$tipo][$idProducto]["codigoBarra"])) ? (($tipo == "noCodificado") ? "PFC-".$_SESSION["usuario"]->getCompañia()."-" : "").$baseProductos[$tipo][$idProducto]["codigoBarra"] : "NULL" ?>">
                                                                <div class="d-flex justify-content-between align-items-center" data-id-producto="<?php echo $value["id"] ?>" data-producto="<?php echo $baseProductos[$tipo][$idProducto]["nombre"] ?>" data-producto-tipo="<?php echo $tipo ?>" data-stock="<?php echo $value["stock"] ?>" data-precio="<?php echo $value["precio"] ?>" data-precio-mayorista="<?php echo $value["precioMayorista"] ?>" data-precio-kiosco="<?php echo $value["precioKiosco"] ?>" data-bar-code="<?php echo (is_numeric($baseProductos[$tipo][$idProducto]["codigoBarra"])) ? (($tipo == "noCodificado") ? "PFC-".$_SESSION["usuario"]->getCompañia()."-" : "").$baseProductos[$tipo][$idProducto]["codigoBarra"] : "NULL" ?>">
                                                                    <div class="d-flex justify-content-between">
                                                                        <div>
                                                                            <?php echo (is_numeric($baseProductos[$tipo][$idProducto]["codigoBarra"])) ? (($tipo == "noCodificado") ? "PFC-".$_SESSION["usuario"]->getCompañia()."-" : "").$baseProductos[$tipo][$idProducto]["codigoBarra"] : "NULL" ?>
                                                                            <?php echo $baseProductos[$tipo][$idProducto]["nombre"] ?>
                                                                        </div>
                                                                        <div class="d-flex justify-content-around"> 
                                                                            <span class="badge badge-primary badge-pill">Stock: <?php echo $value["stock"] ?></span>
                                                                            <span class="badge badge-success badge-pill">Precio: $<?php echo $value["precio"] ?></span>
                                                                        </div>
                                                                    </div>
                                                                    <button type="button" class="btn btn-primary" onclick="ventaProductoRegistro(this)"><i class="fa fa-level-down"></i></button>
                                                                </div>
                                                            </li>
                                                            <?php
                                                        }
                                                    }
                                                ?>
                                            </ul>
                                            <div id="buscador-vacio" class="d-none text-center p-2 font-weight-bold">Producto no encontrado.</div>
                                        </div> 
                                        <div id="container-producto-no-codificado" class="row"> 
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-form-label" for="descripcion">Descripción</label>
                                                    <input type="text" class="form-control" placeholder="VARIOS" value="VARIOS" id="descripcion" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3 align-self-end">
                                                <div class="form-group">
                                                    <label class="control-label">Precio</label>
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">$</span>
                                                            </div>
                                                            <input type="text" id="precio" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 align-self-end">
                                                <button type="button" class="btn btn-success mb-3" onclick="ventaProductoNoCodificadoRegistro()"><i class="fa fa-level-down"></i></button>
                                            </div>
                                        </div>
                                        <table id="tabla-venta-productos" data-sticky-header="true" class="table table-hover table-responsive w-100 tableFixHead"> 
                                            <thead class="sticky-header">
                                                <tr>
                                                    <td class="fit" scope="row"></td>
                                                    <td class="fit" scope="row"><i class="fa fa-barcode"></i> Código</td>
                                                    <td class="w-100 fit">Descripción</td>
                                                    <td class="fit" style="min-width: 210px;">Precio</td>
                                                    <td class="fit" style="min-width: 110px;">Cantidad</td>
                                                    <td class="fit" style="min-width: 140px;">TOTAL</td>
                                                </tr>
                                            </thead>
                                            <tbody id="lista-productos-agregados">
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="text-right align-middle" colspan="5">
                                                        Sub total:
                                                    </td>
                                                    <td>
                                                        $<span id="subtotal">0</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right align-middle" colspan="5">
                                                        DESCUENTO %
                                                    </td>
                                                    <td>
                                                        <input type="number" onchange="cajaCalculaTotal()" onkeyup="cajaCalculaTotal()" class="form-control" placeholder="0" value="0" id="descuento" name="descuento">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right align-middle" colspan="5">
                                                        <fieldset class="form-group">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input class="form-check-input" type="checkbox" id="iva" name="iva" value="1" checked="true">
                                                                    IVA %
                                                                </label>
                                                            </div>
                                                        </fieldset>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" placeholder="21" value="21" id="iva-valor" readonly disabled>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right align-middle" colspan="5">
                                                        TOTAL:
                                                    </td>
                                                    <td>
                                                        $<span id="total">0</span>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div class="w-100 text-right"> 
                                            <button type="button" class="btn btn-outline-primary" onclick="stepper1.next()">Siguiente</button>
                                        </div>
                                    </div>
                                    <div id="venta-p-2" class="content" role="tabpanel" aria-labelledby="venta-p-2-trigger">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex justify-content-between"> 
                                                <div class="w-50">
                                                    <div class="d-flex flex-column">
                                                        <div class="form-group mr-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" onchange="ventaRegistrarFormularioUpdateBusquedaCliente()" id="tipoCliente" checked>
                                                                <label class="custom-control-label" for="tipoCliente" id="tipoClienteLabel">Comprador ocasional</label>
                                                            </div>
                                                        </div>
                                                        <div id="container-cliente" class="form-group flex-grow-1">
                                                            <label for="cliente" class="d-block"><i class="fa fa-search"></i> Buscar cliente</label>
                                                            <select class="form-control" id="cliente" name="cliente">
                                                                <option value=""> - Buscar cliente - </option>
                                                                <?php
                                                                    if(is_array($dataCliente) && count($dataCliente) > 0){
                                                                        foreach($dataCliente AS $key => $value){
                                                                            echo '<option value="'.$value["id"].'">['.$value["documento"].'] '.$value["nombre"].'</option>';
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
                                                </div>
                                                <div class="w-50 d-flex flex-column justify-content-center align-items-center">
                                                    <div class="d-flex justify-content-around w-100 p-1">
                                                        <div id="container-pre-total" class="p-1 text-center h4">
                                                            Total: <br>
                                                            <span class="font-weight-bold d-block w-100">
                                                                $ <span id="pre-total">0</span>
                                                            </span>
                                                        </div>
                                                        <div id="container-pre-pagar" class="p-1 text-center h4">
                                                            A pagar: <br>
                                                            <span class="font-weight-bold d-block w-100">
                                                                $ <span id="pre-pagar">0</span>
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
                                        <div class="d-flex justify-content-between"> 
                                            <button type="button" class="btn btn-outline-primary" onclick="stepper1.previous()">Anterior</button>
                                            <button type="button" onclick="ventaRegistrar(<?php echo $idCaja ?>)" class="btn btn-success">Registrar venta</button>
                                        </div>
                                    </div> 
                                </form>
                            </div>
                        </div>
                    </div>
                    <script>
                        $(document).ready(() => {
                            $("#producto").focus();
                        });
                        
                        var stepper1Node = document.querySelector('#venta-stepper-1')
                        var stepper1 = new Stepper(document.querySelector('#venta-stepper-1'),{
                            linear: false,
                            animation: true
                        });

                        $("#pago").on("change", (e) => {
                            ventaPagoReset();
                            switch(parseInt($("#pago").val())){
                                case 1:
                                    $("#container-pago-1").removeClass("d-none").find("*").removeAttr("disabled"); 
                                    total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
                                    $("#container-pago-1 #monto-contado").val(total).prop("readonly", true);
                                    break;
                                case 2:
                                    $("#container-pago-2").removeClass("d-none").find("*").removeAttr("disabled"); 
                                    total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
                                    $("#container-pago-2 #monto-debito").val(total).prop("readonly", true);
                                    break;
                                case 3:
                                    $("#container-pago-3").removeClass("d-none").find("*").removeAttr("disabled");
                                    total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
                                    $("#container-pago-3 #monto-credito").val(total).prop("readonly", true);
                                    setTimeout(() => { calculaPreTotal('container-pago-3'); }, 350);
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
                                    $("#container-pago-5").removeClass("d-none").find("*").removeAttr("disabled");
                                    break;
                                default:
                                    
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
                                        total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
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
                                            setTimeout(() => { calculaPreTotal('container-pago-5'); }, 350);
                                        }
                                        break;
                                    case 6:
                                        $("#container-pago-6 #monto-debito").val(0);
                                        $("#container-pago-6 #monto-efectivo").val(0)
                                        total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
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
                                            setTimeout(() => { calculaPreTotal('container-pago-6'); }, 350);
                                        }
                                        break;
                                    case 7:
                                        $("#container-pago-7 #monto-contado").val(0);
                                        $("#container-pago-7 #monto-efectivo").val(0);
                                        $("#container-pago-7 #monto-debito").val(0);
                                        total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
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
                                            setTimeout(() => { calculaPreTotal('container-pago-7'); }, 350);
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
                                        total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
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
                                            setTimeout(() => { calculaPreTotal('container-pago-4'); }, 350);
                                        }
                                        break;
                                    case 6:
                                        $("#container-pago-6 #monto-efectivo").val(0);
                                        $("#container-pago-6 #monto-credito").val(0)
                                        total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
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
                                            setTimeout(() => { calculaPreTotal('container-pago-6'); }, 350);
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
                                        total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
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
                                            setTimeout(() => { calculaPreTotal('container-pago-4'); }, 350);
                                        }
                                        break;
                                    case 5:
                                        $("#container-pago-5 #monto-credito").val(0);
                                        $("#container-pago-5 #monto-efectivo").val(0);
                                        total = parseFloat($("#tabla-venta-productos #total").html()).toFixed(2);
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
                                            setTimeout(() => { calculaPreTotal('container-pago-5'); }, 350);
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

                        $("#container-pago-3 #cuota, #container-pago-5 #cuota, #container-pago-6 #cuota").on("change", (e) => { calculaPreTotal('container-pago-'+ $("#pago").val()) });

                        $("#producto").on("keydown", (e) => {
                            let keycode = (e.keyCode ? e.keyCode : e.which);
                            let barcode = $("#producto").val();
                            let strokes = [96,97,98,99,100,101,102,103,104,105];
                            switch(keycode){
                                case 45:
                                    $("#producto").val("PFC-<?php echo $_SESSION["usuario"]->getCompañia() ?>-").focus();    
                                break;
                                case 13:
                                    if(barcode.length > 0){
                                        let lista = [...document.getElementById("container-producto-lista").childNodes];
                                        let search = false;
                                        lista.map((data, i) => {
                                            if(typeof data === 'object' && data.length > 0){
                                                //console.log(i + " " + data.dataset);
                                            }else{
                                                if(data.dataset.barCode === barcode || 'PFC-<?php echo $_SESSION["usuario"]->getCompañia() ?>-' + data.dataset.barCode === barcode){
                                                    $("#" + data.id + " button").click();
                                                    search = true;
                                                }
                                            }
                                        });
                                        if(!search) alert("Producto no encontrado.");
                                    }else{
                                        stepper1.next();
                                        //ventaRegistrar(<?php echo $idCaja ?>);
                                    }
                                break;
                                case 46: 
                                    e.preventDefault();
                                    $("#producto").val("").focus();
                                break;
                            } 
                        });

                        // select the target node
                        var target = document.querySelector('#tabla-venta-productos #total');

                        // create an observer instance
                        var observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                $("#pre-total").html($("#tabla-venta-productos #total").html());
                                $("#pre-pagar").html($("#tabla-venta-productos #total").html());
                                $("#pago").val("");
                                ventaPagoReset();
                                calculaPreTotal('container-pago-'+ $("#pago").val());
                            });
                        });

                        // configuration of the observer:
                        var config = { attributes: true, childList: true, characterData: true }

                        // pass in the target node, as well as the observer options
                        observer.observe(target, config);

                        // later, you can stop observing
                        //observer.disconnect();

                        function ventaProductoRegistro(e){ 
                            setTimeout(() => { 
                                let obj = e.parentElement;
                                let pos = ventaProductoAgregarInput("lista-productos", obj.dataset);
                                cajaCalculaTotalBruto(); 
                                $("#container-producto #producto").val("");
                                $("#container-producto-lista").find("li").css({display: "none"});
                            }, 350);
                        }

                        function ventaProductoNoCodificadoRegistro(){ 
                            let producto = $("#container-producto-no-codificado #descripcion").val();
                            let precio = $("#container-producto-no-codificado #precio").val();
                            let dataset = {
                                "barCode": null,
                                "idProducto": '0',
                                "precio": (precio == '') ? '0' : precio,
                                "producto": (producto == '') ? 'VARIOS' : producto,
                                "stock": null
                            };
                            setTimeout(() => { 
                                let pos = ventaProductoAgregarInput("lista-productos", dataset);
                                cajaCalculaTotalBruto(); 
                            }, 550);
                        }

                        $("#container-producto-no-codificado input").on("keypress", (e) => {
                            let keycode = (e.keyCode ? e.keyCode : e.which);
                            if(keycode == '13'){
                                ventaProductoNoCodificadoRegistro();
                            }
                        });

                        $("#iva").on("change", (e) => {
                            if($("#iva").is(":checked")){
                                $("#iva-valor").val("21");
                            }else{
                                $("#iva-valor").val("0");
                            }
                            cajaCalculaTotal();
                        })

                        tailSelectSet("#cliente");
                        ventaRegistrarFormularioUpdateBusquedaCliente();
                        ventaRegistrarFormularioUpdatetipoProducto();
                    </script>
                    <?php
                }
            }else{
                Sistema::debug('error', 'venta.class.php - registrarFormulario - Usuario no logueado.');
            }   
        }
    }
?>