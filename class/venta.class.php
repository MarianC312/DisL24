<?php
    class Venta {
        public static function historialData($idCaja = null, $fechaInicio = null, $fechaFin = null, $operador = null, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("compañia_sucursal_venta", "*", ((!is_null($idCaja)) ? "caja = '".$idCaja."' AND " : "").((!is_null($fechaInicio) && !is_null($fechaFin)) ? "fechaCarga BETWEEN '".$fechaInicio."' AND '".$fechaFin."' AND " : "").((!is_null($operador)) ? "operador = '".$operador."' AND " : "")."sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "ORDER BY fechaCarga DESC LIMIT 250");
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

        public static function historial(){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $data = Venta::historialData();
                $pago = $_SESSION["lista"]["pago"];
                $productoBase = $_SESSION["lista"]["producto"];
                ?>
                <div class="mine-container">
                    <div class="titulo">Historial de ventas</div>
                    <div class="p-1">
                        <table id="tabla-venta-historial" class="table table-hover table-responsive w-100">
                            <thead>
                                <tr>
                                    <td scope="col">N°</td>
                                    <td class="text-center">Caja</td>
                                    <td>Fecha</td>
                                    <td>Tipo</td>
                                    <td>Monto $</td>
                                    <td class="text-right" style="width: fit-content">Acciones</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(is_array($data)){
                                        if(count($data) > 0){
                                            foreach($data AS $key => $value){ 
                                                ?>
                                                <tr id="venta-<?php echo $key ?>">
                                                    <td><?php echo "N° ".$value["nComprobante"] ?></td>
                                                    <td class="text-center"><?php echo "#".$value["caja"] ?></td>
                                                    <td><?php echo date("d/m/Y", strtotime($value["fechaCarga"]))." ".date("H:i A", strtotime($value["fechaCarga"])) ?></td>
                                                    <td class="w-100"><?php echo $pago[$value["pago"]]["pago"] ?></td>
                                                    <td><?php echo "$".round($value["total"], 2); ?></td>
                                                    <td class="btn-group text-right">
                                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="$('#venta-<?php echo $key ?>-productos').toggleClass('d-none')" title="Expandir detalle de artículos."><i class="fa fa-expand"></i></button>
                                                        <?php
                                                            if(is_numeric($value["nComprobante"]) && $value["nComprobante"] > 0){
                                                                echo '<button type="button" onclick="facturaVisualizar('.$value["id"].')" class="btn btn-sm btn-outline-info"><i class="fa fa-file-pdf-o"></i></button>';
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr id="venta-<?php echo $key ?>-productos" class="d-none">
                                                    <td colspan="6" class="text-center">
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
                                                                <div class="row">
                                                                    <div class="col-md-9 text-right"><?php echo ($iValue == 0) ? "VARIOS" : $productoBase[$tipo][$iValue]["nombre"] ?></div>
                                                                    <div class="col-md-1 text-right"><?php echo $productoCantidad[$iKey]." X "?></div>
                                                                    <div class="col-md-1 text-left"><?php echo " $".$productoPrecio[$iKey] ?></div>
                                                                    <div class="col-md-1"><?php echo "$".round(($productoPrecio[$iKey] * $productoCantidad[$iKey]), 2); ?></div>
                                                                </div>
                                                                <?php
                                                            }
                                                        ?>
                                                    </td>
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
                                                <td colspan="6" class="text-center">No se encontraron registros.</td>
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
                                            <td colspan="6" class="text-center">Hubo un error al recibir la información. <b>Intente nuevamente.</b></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
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
                    dataTableSet("#tabla-venta-historial", false, [[5, 10, 25, 50, 100, -1],[5, 10, 25, 50, 100, "Todos"]], 10, [ 1, "desc" ]);
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
                            $dataProducto = [];
                            $dataCaja = [];
                            $dataCaja["pago"] = $data["pago"];
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
    
                            $dataCaja["total"] = $dataCaja["subtotal"] - ($dataCaja["subtotal"] / 100 * $dataCaja["descuento"]); 
    
                            $nComprobante = Compania::facturaIdUltima(); 
    
                            if(is_numeric($nComprobante) && $nComprobante >= 0){
                                $query = DataBase::insert("compañia_sucursal_venta", "caja,nComprobante,producto,productoCantidad,productoPrecio,pago,descuento,iva,cliente,subtotal,total,operador,sucursal,compañia", "'".$data["idCaja"]."','".($nComprobante + 1)."','".$dataCaja["producto"]."','".$dataCaja["productoCantidad"]."','".$dataCaja["productoPrecio"]."','".$dataCaja["pago"]."','".$dataCaja["descuento"]."','".(($dataCaja["iva"]) ? 1 : 0)."',".((isset($dataCaja["cliente"]) && is_numeric($dataCaja["cliente"]) && $dataCaja["cliente"] > 0) ? $dataCaja["cliente"] : "NULL").",'".$dataCaja["subtotal"]."','".$dataCaja["total"]."','".$_SESSION["usuario"]->getId()."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."'");
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
                                    if($dataCaja["pago"] == 1){
                                        $cajaInsertData = [
                                            "idCaja" => $data["idCaja"],
                                            "tipo" => 5,
                                            "observacion" => "Venta condición contado",
                                            "monto" => $dataCaja["total"],
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
                                    echo '<script>cajaUpdateMonto('.Caja::dataGetMonto($data["idCaja"]).')</script>';
                                    echo '<script>cajaHistorial('.$data["idCaja"].');</script>';
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

        public static function registrarFormulario(){ 
            if(Sistema::usuarioLogueado()){
                Sistema::controlActividadCaja();
                if(Caja::corroboraAcceso()){
                    Session::iniciar();
                    $idCaja = $_SESSION["usuario"]->getActividadCaja();
                    $dataCliente = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["cliente"];
                    $baseProductos = $_SESSION["lista"]["producto"];
                    $dataStock = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"];
                    ?>
                    <div id="container-venta-formulario" class="mine-container">
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
                        <form id="venta-registro-form" action="./engine/venta/registrar.php" form="#venta-registro-form" process="#venta-registro-process">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex">
                                        <div class="form-grou mr-2">
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
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pago">Forma de pago</label>
                                        <select class="form-control" id="pago" name="pago">
                                            <?php
                                                if(is_array($_SESSION["lista"]["pago"]) && count($_SESSION["lista"]["pago"]) > 0){
                                                    foreach($_SESSION["lista"]["pago"] AS $key => $value){
                                                        echo '<option value="'.$key.'">'.$value["pago"].'</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div> 
                                </div>
                            </div>
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
                            <div class="form-group">
                                <button type="button" onclick="ventaRegistrar(<?php echo $idCaja ?>)" class="btn btn-success">Registrar venta</button>
                            </div>
                        </form>
                    </div>
                    <script>
                        $(document).ready(() => {
                            $("#producto").focus();
                        })

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
                                        ventaRegistrar(<?php echo $idCaja ?>);
                                    }
                                break;
                                case 46: 
                                    e.preventDefault();
                                    $("#producto").val("").focus();
                                break;
                            }
                            
                        });

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