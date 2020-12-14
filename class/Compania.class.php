<?php
    class Compania{ 
        public static function corroboraExistencia($idCompañia){
            if(Sistema::usuarioLogueado()){
                if(isset($idCompañia) && is_numeric($idCompañia) && $idCompañia > 0){
                    $query = DataBase::select("compañia", "id", "id = '".$idCompañia."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            return true;
                        }else{
                            Sistema::debug('error', 'compania.class.php - corroboraExistencia - Compañía no encontrada. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'compania.class.php - corroboraExistencia - Error al consultar la información de la compañía. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - corroboraExistencia - Error en identificador de compañía. Ref.: '.$idCompañia);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - corroboraExistencia - Usuario no logueado.');
            }
            return false;
        }

        public static function reloadStaticData(){ 
            if(Sistema::usuarioLogueado()){
                $_SESSION["lista"]["compañia"]["cliente"] = Lista::compañiaCliente();
                $_SESSION["lista"]["compañia"]["sucursal"]["stock"] = Compania::stockData();
            }else{
                Sistema::debug('error', 'compania.class.php - reloadStaticData - Usuario no logueado.');
            }
        }

        public static function facturaIdUltima($sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("compañia_sucursal_venta", "nComprobante", "sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "ORDER BY cast(nComprobante as unsigned) DESC LIMIT 1");
                if($query){
                    if(DataBase::getNumRows($query) == 1){
                        $dataQuery = DataBase::getArray($query);
                        return $dataQuery["nComprobante"];
                    }else{
                        Sistema::debug('info', 'compania.class.php - facturaIdUltima - No se encontro información. Ref.: '.DataBase::getNumRows($query));
                        return 0;
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - facturaIdUltima - No se pudo comprobar la información. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('error', 'compania.class.php - facturaIdUltima - Usuario no logueado.');
            }
            return false;
        }

        public static function facturaData($idVenta, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if((isset($idVenta) && is_numeric($idVenta) && $idVenta > 0)){ 
                    $query = DataBase::select("compañia_sucursal_venta", "*", "id = '".$idVenta."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
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
                        Sistema::debug('error', 'compania.class.php - facturaData - Error al comprobar la información. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - facturaData - Error en identificador de venta. Ref.: '.$idVenta);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - facturaData - Usuario no logueado.');
            }
            return false;
        }

        public static function facturacion($idCompañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $idCompañia = (is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia();
                if(isset($idCompañia) && is_numeric($idCompañia) && $idCompañia > 0){
                    if(Compania::corroboraExistencia($idCompañia)){
                        $data = $_SESSION["lista"]["compañia"][$idCompañia]["sucursal"]["facturacion"];
                        unset($data["pendiente"]);
                        if(is_array($data)){
                            ?>
                            <div class="mine-container">
                                <div class="d-flex justify-content-between">
                                    <div class="titulo">Facturación</div> 
                                </div> 
                                <?php
                                    $mensaje['tipo'] = 'info';
                                    $mensaje['cuerpo'] = 'La cancelación de las facturas adeudadas será por depósito bancario:<br><b>BBVA</b><br><b>CBU:</b> 0170282040000034200724<br><br>Una vez realizado el depósito adjuntar el comprobante de pago a la dirección de email <a href="mailto:contacto@efecesoluciones.com.ar">contacto@efecesoluciones.com.ar</a>';
                                    Alert::mensaje($mensaje);
                                ?>
                                <table id="tabla-facturacion" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">Recibo</th>
                                            <th>Estado</th>
                                            <th>Total</th>
                                            <th>Creado</th>
                                            <th>Pagado el</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            if(count($data) > 0){
                                                foreach($data AS $key => $value){
                                                    ?>
                                                    <tr>
                                                        <td><a href="./administracion/documentacion/compania/<?php echo $idCompañia ?>/facturacion/<?php echo $value["file"] ?>" download=""><?php echo mb_strtoupper($value["recibo"]) ?> <i class="fa fa-download"></i></a></td>
                                                        <td class="text-<?php echo Administracion::$facturaEstadoClass[$value["estado"]] ?>"><?php echo Administracion::$facturaEstado[$value["estado"]] ?></td>
                                                        <td>AR$ <?php echo $value["total"] ?></td>
                                                        <td><?php echo date("d/m/Y H:i A", strtotime($value["fechaCarga"])) ?></td>
                                                        <td><?php echo (!is_null($value["fechaPago"])) ? date("d/m/Y H:i A", strtotime($value["fechaPago"])) : "&nbsp;" ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            }else{
                                                ?>
                                                <td colspan="5" class="text-center">La compañía no tiene facturación asociada.</td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <?php
                                            }
                                        ?>
                                    </tbody>
                                </table>
                                <script>
                                    dataTableSet("#tabla-facturacion");
                                </script>
                            </div>
                            <?php
                        }else{
                            Sistema::debug('error', 'compania.class.php - clienteFacturacionGestion - Hubo un error al recibir la información de la facturación.');
                            $mensaje['tipo'] = 'warning';
                            $mensaje['cuerpo'] = 'Hubo un error al recibir la información de la facturación de la compañía. <b>Intente nuevamente o contacte al administrador.</b>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        Sistema::debug('info', 'compania.class.php - clienteFacturacionGestion - No se encontró la compañía con el identificador brindado. Ref.: '.$idCompañia);
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'No se encontró la compañía.';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - clienteFacturacionGestion - Identificador de oficina erroneo. Ref.: '.$idCompañia);
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información de la compañía. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - clienteFacturacionGestion - Usuario no logueado.');
            }
        } 

        public static function facturacionData($idCompañia, $estado = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                if(isset($idCompañia) && is_numeric($idCompañia) && $idCompañia > 0){
                    if(Compania::corroboraExistencia($idCompañia)){
                        $query = DataBase::select("sistema_compañia_facturacion", "*", ((is_numeric($estado)) ? "estado = '".$estado."'" : "1")." AND compañia = '".$idCompañia."'", "ORDER BY estado ASC, fechaCarga DESC");
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
                            Sistema::debug('error', 'administracion.class.php - clienteFacturacionData - Hubo un error al buscar la información de la facturación de la compañía. Ref.: '.DataBase::getError());
                        }
                    }else{
                        Sistema::debug('info', 'administracion.class.php - clienteFacturacionData - No se encontró la compañía con el identificador brindado. Ref.: '.$idCompañia); 
                    }
                }else{
                    Sistema::debug('error', 'administracion.class.php - clienteFacturacionData - Identificador de oficina erroneo. Ref.: '.$idCompañia); 
                }
            }else{
                Sistema::debug('error', 'administracion.class.php - clienteFacturacionData - Usuario no logueado.');
            }
            return false;
        }

        public static function data($compañia = null){
            if(Sistema::usuarioLogueado()){
                $query = DataBase::select("compañia", "*", "id = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
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
                    Sistema::debug('error', 'compania.class.php - data - Error al buscar la información de la compañia. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('error', 'compania.class.php - data - Usuario no logueado.');
            }
            return false;
        }

        public static function facturaVisualizar($idVenta, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if((isset($idVenta) && is_numeric($idVenta) && $idVenta > 0)){ 
                    $data = Compania::facturaData($idVenta, $sucursal, $compañia);
                    if(is_array($data)){
                        Session::iniciar();
                        $dataCompañia = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["data"][$_SESSION["usuario"]->getCompañia()];
                        $dataCompañiaStock = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"];
                        $prenComprobante = "";
                        for($i = 12; $i >= strlen($data[$idVenta]["nComprobante"]); $i--){
                            $prenComprobante .= "0";
                        }
                        if(count($data) > 0){
                            $producto = explode(",", $data[$idVenta]["producto"]);
                            $productoCantidad = explode(",", $data[$idVenta]["productoCantidad"]);
                            $productoPrecio = explode(",", $data[$idVenta]["productoPrecio"]);
                            ?>
                            <div class="d-flex justify-content-end">
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
                                    <div style="display: flex; flex-direction: column; padding: 1.5em 0;">
                                        <div style="font-size: 1.3em; font-weight: bold;">
                                            Cliente
                                        </div>
                                        <?php
                                            if(is_numeric($data[$idVenta]["cliente"]) && $data[$idVenta]["cliente"] > 0){
                                                $dataCliente = Cliente::data(["filtroOpcion" => 3, "id" => $data[$idVenta]["cliente"]]);
                                                ?>
                                                <span><b>Nombre y apellido:</b> <?php echo $dataCliente[$data[$idVenta]["cliente"]]["nombre"] ?></span>
                                                <span><b>N° Documento:</b> <?php echo $dataCliente[$data[$idVenta]["cliente"]]["documento"] ?></span>
                                                <span><b>N° Teléfono:</b> <?php echo $dataCliente[$data[$idVenta]["cliente"]]["telefono"] ?></span>
                                                <span><b>Domicilio:</b> <?php echo $dataCliente[$data[$idVenta]["cliente"]]["domicilio"] ?></span>
                                                <span><b>Email:</b> <?php echo $dataCliente[$data[$idVenta]["cliente"]]["email"] ?></span>
                                                <?php
                                            }else{
                                                ?>
                                                <span>Cliente ocasional.</span>
                                                <?php
                                            }
                                        ?>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end">
                                        <span><b>Fecha: </b><?php echo date("d/m/Y", strtotime($data[$idVenta]["fechaCarga"])) ?></span>
                                        <span><b>Hora: </b><?php echo date("H:i A", strtotime($data[$idVenta]["fechaCarga"])) ?></span>
                                        <span><b>Comprobante N°:</b> #<?php echo $prenComprobante.$data[$idVenta]["nComprobante"] ?></span>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: center; align-items: center; padding: 0.375em">
                                    <b>COMPROBANTE NO FISCAL</b>
                                </div>
                                <div>
                                    <table style="border-top: 1px solid lightgray; border-bottom: 1px solid lightgray; width: 100%;">
                                        <thead style="border-top: 1px solid lightgray; border-bottom: 1px solid lightgray;">
                                            <tr style="text-align: center; font-weight: bold; background-color: burlywood;">
                                                <td style="width: 66%; padding: 1.1em;">Descripción</td>
                                                <td style="width: 6%; padding: 1.1em">Cant.</td>
                                                <td style="width: 12%; padding: 1.1em">Precio/U.</td>
                                                <td style="width: 15%; padding: 1.1em; text-align: right">Total</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $total = 0;
                                                foreach($producto AS $key => $value){
                                                    $tipo = ($value[0] == "*") ? "noCodificado" : "codificado";
                                                    $value = str_replace("*", "", $value);
                                                    ?>
                                                    <tr style="border-bottom: 1px solid lightgray;">
                                                        <td style="padding: 1.015em 0; "><?php echo ($value == 0) ? "VARIOS" : $_SESSION["lista"]["producto"][$tipo][$dataCompañiaStock[$value][($tipo == "codificado") ? "producto" : "productoNC"]]["nombre"] ?></td>
                                                        <td style="padding: 1.015em 0; text-align: center;"><?php echo $productoCantidad[$key] ?></td>
                                                        <td style="padding: 1.015em 0; text-align: center;">$<span><?php echo $productoPrecio[$key] ?></span></td>
                                                        <td style="padding: 1.015em 0; text-align: right;">$<span><?php echo round($productoCantidad[$key] * $productoPrecio[$key], 2) ?></span></td>
                                                    </tr>
                                                    <?php
                                                    $total += $productoCantidad[$key] * $productoPrecio[$key];
                                                }
                                            ?>
                                        </tbody>
                                        <tfoot style="border-top: 1px solid lightgray; border-bottom: 1px solid lightgray; ">
                                            <tr>
                                                <td style="padding: 1.015em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Subtotal:</td>
                                                <td style="padding: 1.015em 0; text-align: right">$ <?php echo round($total - ($total / 100 * 21), 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 1.015em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Descuento:</td>
                                                <td style="padding: 1.015em 0; text-align: right">% <?php echo $data[$idVenta]["descuento"] ?></td>
                                            </tr>
                                            <?php
                                                if($data[$idVenta]["iva"] == 1){
                                                    ?> 
                                                    <tr>
                                                        <td style="padding: 1.015em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Iva :</td>
                                                        <td style="padding: 1.015em 0; text-align: right">% 21</td>
                                                    </tr>
                                                    <?php
                                                }
                                            ?>
                                            <tr>
                                                <td style="padding: 1.015em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Total:</td>
                                                <td style="padding: 1.015em 0; text-align: right">$ <?php echo round($total - ($total / 100 * $data[$idVenta]["descuento"]) - ($data[$idVenta]["descuento"] / 100 * (($data[$idVenta]["iva"] == 1) ? 21 : 0)), 2) ?></td>
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
                            $mensaje['cuerpo'] = 'No se recibió información de la factura. <b>Intente nuevamente o contacte al administrador.</b>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al recibir la información de la factura. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al generar el recibo. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('error', 'compania.class.php - facturaVisualizar - Error en identificador de venta. Ref.: '.$idVenta);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - facturaVisualizar - Usuario no logueado.');
            }
        }

        public static function stockRestar($producto, $productoCantidad, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($producto) && !is_null($producto) && strlen($producto) > 0){
                    Session::iniciar();
                    $response = [];
                    $dataStock = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"];
                    $producto = explode(",", str_replace("*", "", $producto)); 
                    if(isset($productoCantidad) && !is_null($productoCantidad) && strlen($productoCantidad) > 0){
                        $productoCantidad = explode(",", $productoCantidad);
                        foreach($producto AS $key => $value){
                            if($value != 0){
                                if($dataStock[$value]["stock"] >= $productoCantidad[$key]){
                                    $query = DataBase::update("producto_stock", "stock = stock - '".$productoCantidad[$key]."', operador = '".$_SESSION["usuario"]->getId()."'", "id = '".$value."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'"); 
                                    $response[$key]["id"] = $value;
                                    $response[$key]["cantidad"] = $productoCantidad[$key];
                                    if($query){
                                        $response[$key]["status"] = true; 
                                    }else{
                                        $response[$key]["status"] = false;
                                        Sistema::debug('error', 'compania.class.php - stockRestar - No se pudo restar el stock al producto en stock N° '.$value.'. Ref.: '.DataBase::getError());
                                    }
                                }else{
                                    Sistema::debug('error', 'compania.class.php - stockRestar - El producto '.$dataStock[$value]["nombre"].' ['.$value.'] no tiene stock disponible. Stock disponible: '.$dataStock[$key]["stock"].' - Cantidad solicitada: '.$productoCantidad[$key]);
                                }
                            }else{
                                $response[$key]["status"] = true;
                            }
                        }
                        return $response;
                    }
                }else{ 
                    Sistema::debug('error', 'compania.class.php - stockRestar - Lista de productos nula. Ref.: '.$producto);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockRestar - Usuario no logueado.');
            }
            return false;
        }

        public static function stockRegistroProductoListaFormulario($formData, $max = 200){
            if(Sistema::usuarioLogueado()){ 
                $data = Producto::buscadorData($formData, $max);
                $stock = Compania::stockData();
                $stockProducto = [];
                foreach($stock AS $key => $value){
                    array_push($stockProducto, $value["producto"]);
                }
                ?>
                <div id="stock-registro-producto-lista-resultado" class="mine-container">
                    <div class="d-flex justify-content-between">
                        <div class="titulo">Base de productos</div>
                        <button type="button" onclick="$('#stock-registro-producto-lista-resultado').remove()" class="btn delete"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="p-1" style="overflow: auto;">
                        <table id="tabla-producto-inventario" class="table table-hover">
                            <thead>
                                <tr>
                                    <th id="tag-codigo" scope="col">Código</th>
                                    <th id="tag-producto" scope="col">Producto</th>
                                    <th id="tag-stock" class="text-center" scope="col">Stock</th>
                                    <th id="tag-minimo" class="text-center" scope="col">S. Mínimo</th>
                                    <th id="tag-maximo" class="text-center" scope="col">S. Máximo</th>
                                    <th id="tag-preu" scope="col">Precio x U.</th>
                                    <th id="tag-prem" scope="col">Precio May.</th>
                                    <th id="tag-prek" scope="col">Precio Kiosco</th>
                                    <th id="tag-tipo" scope="col">Tipo</th>
                                    <th id="tag-categoria" scope="col">Categoría</th>
                                    <th scope="col">Sub-Categoría</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(is_array($data)){
                                        if(count($data) > 0){
                                            $productoTipo = $_SESSION["lista"]["producto"]["tipo"];
                                            $productoCategoria = $_SESSION["lista"]["producto"]["categoria"];
                                            $productoSubcategoria = $_SESSION["lista"]["producto"]["subcategoria"]; 
                                            $counter = 0;
                                            foreach($data AS $key => $value){
                                                echo '<pre>';
                                                print_r($value);
                                                echo '</pre>';
                                                $enStock = (Sistema::in_array_r($value["id"], $stockProducto)) ? true : false;
                                                if($enStock){
                                                    $stockKey = "";
                                                    foreach($stock AS $iKey => $iValue){
                                                        if(in_array($value["id"], $iValue)){
                                                            $stockKey = $iKey;
                                                        }
                                                    }
                                                    $prodStock = $stock[$stockKey]["stock"];
                                                    $prodMin = $stock[$stockKey]["minimo"];
                                                    $prodMax = $stock[$stockKey]["maximo"];
                                                    $prodPrecio = $stock[$stockKey]["precio"];
                                                    $prodPrecioMayorista = $stock[$stockKey]["precioMayorista"];
                                                    $prodPrecioKiosco = $stock[$stockKey]["precioKiosco"];
                                                }else{
                                                    $prodStock = null;
                                                    $prodMin = null;
                                                    $prodMax = null;
                                                    $prodPrecio = null;
                                                    $prodPrecioMayorista = null;
                                                    $prodPrecioKiosco = null;
                                                }
                                                ?>
                                                <tr id="producto-<?php echo $value["id"] ?>" data-key="<?php echo $value["id"] ?>">
                                                    <th scope="row"><?php echo $value["codigoBarra"] ?></th>
                                                    <td style="display: flex; flex-direction: column-reverse;"><?php echo (($enStock) ? '<span class="badge badge-success" style="width: fit-content;"><i class="fa fa-check-square-o"></i> En stock</span> ' : '').$value["nombre"] ?></td>
                                                    <td id="stock" data-value="<?php echo (isset($prodStock) && is_numeric($prodStock)) ? $prodStock : 0 ?>" class="text-center <?php echo (!$enStock) ? "opacity-0" : "" ?>"><?php echo (isset($prodStock)) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$prodStock.'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i> stock inicial</a>" ?></td>
                                                    <td id="minimo" data-value="<?php echo (isset($prodMin) && is_numeric($prodMin)) ? $prodMin : 0 ?>" class="text-center <?php echo (!$enStock) ? "opacity-0" : "" ?>"><?php echo (isset($prodMin) && is_numeric($prodMin)) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$prodMin.'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="maximo" data-value="<?php echo (isset($prodMax) && is_numeric($prodMax)) ? $prodMax : 0 ?>" class="text-center <?php echo (!$enStock) ? "opacity-0" : "" ?>"><?php echo (isset($prodMax) && is_numeric($prodMax)) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$prodMax.'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="precio" data-value="<?php echo (isset($prodPrecio) && is_numeric($prodPrecio)) ? $prodPrecio : "$0" ?>" class="text-center <?php echo (!$enStock) ? "opacity-0" : "" ?>"><?php echo (isset($prodPrecio) && is_numeric($prodPrecio)) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$'.$prodPrecio.'</span> <i class="fa fa-pencil"></i></button>' : '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$0</span> <i class="fa fa-pencil"></i></button>' ?></td>
                                                    <td id="precioMayorista" data-value="<?php echo (isset($prodPrecioMayorista) && is_numeric($prodPrecioMayorista)) ? $prodPrecioMayorista : "$0" ?>" class="text-center <?php echo (!$enStock) ? "opacity-0" : "" ?>"><?php echo (isset($prodPrecioMayorista) && is_numeric($prodPrecioMayorista)) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$'.$prodPrecioMayorista.'</span> <i class="fa fa-pencil"></i></button>' : '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$0</span> <i class="fa fa-pencil"></i></button>' ?></td>
                                                    <td id="precioKiosco" data-value="<?php echo (isset($prodPrecioKiosco) && is_numeric($prodPrecioKiosco)) ? $prodPrecioKiosco : "$0" ?>" class="text-center <?php echo (!$enStock) ? "opacity-0" : "" ?>"><?php echo (isset($prodPrecioKiosco) && is_numeric($prodPrecioKiosco)) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$'.$prodPrecioKiosco.'</span> <i class="fa fa-pencil"></i></button>' : '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$0</span> <i class="fa fa-pencil"></i></button>' ?></td>
                                                    <td><?php echo $productoTipo[$value["tipo"]]; ?></td>
                                                    <td><?php echo $productoCategoria[$value["categoria"]] ?></td>
                                                    <td><?php echo (is_numeric($value["subcategoria"])) ? $productoSubcategoria[$value["subcategoria"]] : "<span class='text-muted'>No categorizado</span>" ?></td>
                                                    <td>
                                                        <?php
                                                            echo (!$enStock) ? '<button type="button" onclick="stockRegistroPRoductoListaFormularioSetStock('.$value['id'].')" class="btn btn-outline-info"><i class="fa fa-plus"></i> Agregar a mi stock</button>' : '';
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                                $counter++;
                                                if($counter == 500){
                                                    ?>
                                                    <tr>
                                                        <td colspan="12" class="text-center">
                                                            Cargar más
                                                        </td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                        <td class="d-none"></td>
                                                    </tr>
                                                    <?php
                                                    break;
                                                }
                                            } 
                                        }else{
                                            ?> 
                                            <tr>
                                                <td colspan="12" class="text-center">
                                                    <b>No se encontraron productos.</b> <br> 
                                                    <?php
                                                        if($formData["filtroOpcion"] == 1){
                                                            $mensaje['tipo'] = 'info';
                                                            $mensaje['cuerpo'] = 'Intenta nuevamente con una combinación distinta.<br>
                                                            Utilizá <u>palabras incompletas</u> para identificar mejor a los productos, por ejemplo si querés
                                                            buscar a acondicionadores <b>te recomendamos</b> ingresar "acond" o "acondic" <u>para tener más posibilidades</u> de que la búsqueda sea satisfactoria. <br><br>
                                                            <b>Si luego de varios intentos el producto no aparece, intentá buscarlo con el código de barra seleccionando la opción "Filtrar por Código de barra <i class="fa fa-barcode"></i>"</b>';
                                                            Alert::mensajeSmall($mensaje);
                                                        }else{
                                                            $mensaje['tipo'] = 'info';
                                                            $mensaje['cuerpo'] = 'Intenta nuevamente una parte del código de barra.<br> Utilizá <u>parte del código de barra</u> para identificar mejor a los productos, por ejemplo si querés
                                                            buscar una serie de productos de un importador o productor <b>te recomendamos</b> ingresar hasta <u>8 números del código</u> para que la búsqueda sea satisfactoria. <br><br><b>Para registrar este producto ingresá a este <a href="#" onclick="productoRegistroFormulario(false,'.$formData["codigo"].')">LINK</a></b>';
                                                            Alert::mensajeSmall($mensaje);
                                                        }
                                                    ?>
                                                </td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
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
                                        if(is_bool($data) && !$data){
                                            Sistema::debug('error', 'producto.class.php - inventario - Data boolean FALSE.');
                                        }else{
                                            Sistema::debug('error', 'producto.class.php - inventario - Error desconocido.');
                                        }
                                        ?> 
                                        <tr>
                                            <td colspan="12" class="text-center">
                                                Hubo un error al encontrar los productos de la compañía. <b>Intente nuevamente o contacte al administrador.</b>
                                            </td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <script> 
                        $(document).ready(function() {
                            let cantidad = <?php echo (is_array($data)) ? count($data) : null; ?>;
                            if(!isNaN(cantidad) && cantidad === 0) productoRegistroFormulario(false, <?php echo $formData["codigo"] ?>);
                            $('td a').on('click', (e) => {
                                productoInventarioEditarContenidoFormulario(e.currentTarget.parentNode.parentNode.getAttribute("data-key"),e.currentTarget.parentNode.getAttribute("id"),e.currentTarget.parentNode.getAttribute("data-value"));
                            });
                            $('td>button').on('click', (e) => {
                                productoInventarioEditarContenidoFormulario(e.currentTarget.parentNode.parentNode.getAttribute("data-key"),e.currentTarget.parentNode.getAttribute("id"),e.currentTarget.parentNode.getAttribute("data-value"));
                            });
                            tippy('td a', {
                                content: 'Click para agregar un nuevo valor.',
                                delay: [150,150],
                                animation: 'fade'
                            });
                            tippy('td button', {
                                content: 'Click para modificar el valor.',
                                delay: [150,150],
                                animation: 'fade'
                            });
                            tippy('#tag-codigo', {
                                content: '<i class="fa fa-barcode"></i> Código de barra del producto.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-producto', {
                                content: 'Nombre y descripción del artículo.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-stock', {
                                content: 'Cantidad de artículo en stock.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-minimo', {
                                content: 'Stock mínimo es un parámetro definido para el sistema, con el cual se le dará una notificación cuando este artículo alcance valores inferios al guardado.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-maximo', {
                                content: 'Stock máximo al contrario de "Stock Mínimo", dará una notificación cuando este artículo supere al valor guardado.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-preu', {
                                content: 'Precio por unidad del artículo.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-prem', {
                                content: 'Precio al por mayor del artículo.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-prek', {
                                content: 'Precio de kiosco del artículo.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-tipo', {
                                content: 'Tipo de producto',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-categoria', {
                                content: 'Categoría de producto',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            $('#tabla-producto-inventario').DataTable({
                                "sDom": '<"d-flex justify-content-between"lfp>rt<"d-flex justify-content-between"ip><"clear">',
                                "lengthMenu": [ [4, 8, 25, 50, 100, -1], [4, 8, 25, 50, 100, "Todos"] ],
                                "pageLength": 8,
                                "bSort": true,
                                "language": {
                                    "decimal":        "",
                                    "emptyTable":     "No hay información para mostrar.",
                                    "info":           "Mostrando página _PAGE_ de _PAGES_",
                                    "infoEmpty":      "Mostrando 0 a 0 de 0 registros",
                                    "infoFiltered":   "(filtrado de _MAX_ total de registros)",
                                    "infoPostFix":    "",
                                    "thousands":      ",",
                                    "lengthMenu":     "Mostrar _MENU_ registros.",
                                    "loadingRecords": "Cargando...",
                                    "processing":     "Procesando...",
                                    "search":         "Buscar:",
                                    "zeroRecords":    "No se encontraron coincidencias.",
                                    "paginate": {
                                        "first":      "Primero",
                                        "last":       "Último",
                                        "next":       "Siguiente",
                                        "previous":   "Anterior"
                                    },
                                    "aria": {
                                        "sortAscending":  ": activar para ordenar ascendentemente",
                                        "sortDescending": ": activar para ordenar descendientemente"
                                    }
                                }
                            });
                        } ); 
                    </script>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'compania.class.php - stockRegistroProductoListaFormulario - Usuario no logueado.');
            }
        }

        public static function stockRegistroProductoFormulario($codigoBarra = null){
            if(Sistema::usuarioLogueado()){
                ?>
                <div class="mine-container">
                    <div class="titulo">Registrar un producto de la base de productos en mi stock:</div>
                    <form id="compania-stock-registro-producto-form" onsubmit="return false;" action="./includes/compania/stock-registro-producto-lista-formulario.php" form="#compania-stock-registro-producto-form" process="#compania-stock-registro-producto-process">
                        <fieldset class="form-group">
                            <div class="d-flex justify-content-around flex-row-reverse">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" onchange="compañiaRegistroProductoUpdateBusqueda()" name="filtroOpcion" id="filtroOpcion1" value="1">
                                        Filtrar por Etiquetas <i class="fa fa-tag"></i>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" onchange="compañiaRegistroProductoUpdateBusqueda()" name="filtroOpcion" id="filtroOpcion2" value="2" checked="">
                                        Filtrar por Código de barra <i class="fa fa-barcode"></i>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                        <div id="container-codigo" class="form-group" style="display: none">
                            <label class="col-form-label" for="codigo"><i class="fa fa-barcode"></i> Código de barra</label>
                            <input type="text" class="form-control" placeholder="Ingresá el código del producto" id="codigo" name="codigo" value="<?php echo $codigoBarra ?>">
                            <small class="text-muted">Utilizá una parte del código para obtener un listado ámplio del proveedor/importador. Conocé más <a href="#/">acá</a></small>
                        </div>
                        <div id="container-tag" class="form-group" style="display: none">
                            <label class="col-form-label" for="tag"><i class="fa fa-tag"></i> Filtro</label>
                            <div class="input-group"> 
                                <input type="text" class="form-control" placeholder="Ej.: shampoo, sedal, 200" id="tag" autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                            <small class="text-muted">Utilizá combinaciones como "rollo, cocina, x3" o "acond, elvive, 400". Conocé más formas de buscar <a href="#/">acá</a>.</small>
                        </div>
                        <div id="tag-badge"></div>
                        <div id="tag-agregadas" class="form-group"></div>
                        <div class="form-group">
                            <button type="button" onclick="compañiaStockRegistroProductoListaFormulario()" class="btn btn-primary btn-iconed">Buscar producto <i class="fa fa-search"></i></button>
                        </div>
                    </form>
                    <div id="compania-stock-registro-producto-process" style="display: none"></div>
                </div>
                <script> 
                    $(document).ready(() => {

                        if ($('#filtroOpcion1').is(':checked')) {
                            $("#tag").focus();
                        }
                        if ($('#filtroOpcion2').is(':checked')) {
                            $("#codigo").focus();
                        }
                        compañiaRegistroProductoUpdateBusqueda();

                        let codigoBarra = "<?php echo $codigoBarra; ?>";
                        if(codigoBarra !== null && codigoBarra.length > 0){ compañiaStockRegistroProductoListaFormulario(); }
                    })
                    $("#compania-stock-registro-producto-form #container-tag input").on("keypress", (e) => {
                        let keycode = (e.keyCode ? e.keyCode : e.which);
                        if(keycode == '13'){
                            let input = $("#compania-stock-registro-producto-form #container-tag input");
                            if(input.val() == ""){
                                alert("Ingrese un valor a buscar.");
                            }else{
                                agregarInput(input.attr("id"),input.val());
                                input.val("");
                            }
                        }
                    });
                    $("#compania-stock-registro-producto-form #container-tag button").on("click", (e) => {
                        let input = $("#compania-stock-registro-producto-form #container-tag input");
                        agregarInput(input.attr("id"),input.val());
                        input.val("");
                    });
                    $("#compania-stock-registro-producto-form #codigo").keyup((e) => {
                        var keycode = (e.keyCode ? e.keyCode : e.which);
                        if(keycode == '13'){
                            compañiaStockRegistroProductoListaFormulario()
                        }
                    });
                </script>
                <?php
            }else{
                Sistema::debug('error', 'compania.class.php - stockRegistroProductoFormulario - Usuario no logueado.');
            }
        }

        public static function stockGetData($idProducto, $tipo = null, $sucursal = null, $compañia = null, $productoTipo = "codificado"){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    Session::iniciar();
                    $query = DataBase::select("producto_stock", ((!is_null($tipo)) ? $tipo : "*"), (($productoTipo == "codificado") ? "producto" : "productoNC")." = '".$idProducto."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            if(!is_null($tipo) && count(explode(",", $tipo)) < 1){
                                return $dataQuery[$tipo];
                            }else{
                                foreach($dataQuery AS $key => $value){
                                    if(is_int($key)){
                                        unset($dataQuery[$key]);
                                    }
                                }
                                return $dataQuery;
                            }
                        }else{
                            Sistema::debug('error', 'compania.class.php - stockGetData - No se encontró la información del stock del producto. Ref.: '.$idProducto);
                            return 0;
                        }
                    }else{
                        Sistema::debug('error', 'compania.class.php - stockGetData - Hubo un error al buscar la información del stock del producto. Ref.: '.$idProducto);
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - stockGetData - Error en el identificador de producto o tipo de dato. Ref.: [ID => '.$idProducto.', TIPO => '.$tipo.']');
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockGetData - Usuario no logueado.');
            }
            return false;
        }

        public static function stockContenido($idProducto, $tipo, $productoTipo = "codificado"){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0 && isset($tipo) && strlen($tipo) > 0){
                    $data = Compania::stockGetData($idProducto, $tipo, null, null, $productoTipo);
                    if(is_numeric($data[$tipo])){
                        echo '<button type="button" class="btn btn-sm btn-link btn-iconed p-0"><span class="spn">'.(($tipo === "precio") ? "$" : "").$data[$tipo].'</span> <i class="fa fa-pencil"></i></button>';
                        ?>
                        <script>
                            $(document).ready(() => {
                                $('#producto-<?php echo $idProducto ?> #<?php echo $tipo ?> button').on('click', (e) => {
                                    compañiaStockEditarContenidoFormulario(<?php echo $idProducto ?>,e.currentTarget.parentNode.getAttribute("id"),<?php echo $data[$tipo] ?>);
                                });
                            })
                        </script>
                        <?php
                    }else{
                        Sistema::debug('error', 'compania.class.php - stockContenido - Información de stock erronea. Ref.: '.$data[$tipo]);
                        echo '<button onclick="successAction(\'#producto-'.$idProducto.' #'.$tipo.'\', () => { compañiaStockContenidoData('.$idProducto.', \''.$tipo.'\') })" class="btn btn-info"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - stockContenido - Error en el identificador de producto o tipo de dato. Ref.: [ID => '.$idProducto.', TIPO => '.$tipo.']');
                    echo '<button onclick="successAction(\'#producto-'.$idProducto.' #'.$tipo.'\', () => { compañiaStockContenidoData('.$idProducto.', \''.$tipo.'\') })" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockContenido - Usuario no logueado.');
            }
        }

        public static function stockGetId($idProducto, $sucursal = null, $compañia = null, $productoTipo = "codificado"){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    $query = DataBase::select("producto_stock", "id", (($productoTipo == "codificado") ? "producto" : "productoNC")." = '".$idProducto."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return $dataQuery["id"];
                        }else{
                            Sistema::debug('info', 'compania.class.php - stockGetId - No se encontró el producto en stock.');
                        }
                    }else{
                        Sistema::debug('error', 'compania.class.php - stockGetId - Error al comprobar la existencia del stock.');
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - stockGetId - Identificador de producto incorrecto. Ref.: '.$idProducto);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockGetId - Usuario no logueado.');
            }
            return false;
        }

        public static function stockCorroboraExistencia($idProducto, $sucursal = null, $compañia = null, $productoTipo = "codificado"){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    Session::iniciar();
                    $query = DataBase::select("producto_stock", "id", (($productoTipo == "codificado") ? "producto" : "productoNC")." = '".$idProducto."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        return (DataBase::getNumRows($query) == 1) ? true : 0;
                    }else{
                        Sistema::debug('error', 'compania.class.php - stockCorroboraExistencia - Error al comprobar la existencia del stock.');
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - stockCorroboraExistencia - Identificador de producto incorrecto. Ref.: '.$idProducto); 
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockCorroboraExistencia - Usuario no logueado.');
            }
            return false;
        }

        public static function stockEditarContenido($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    echo Sistema::loading();
                    $codigoProducto = Producto::getCodigo($data["idProducto"]);
                    if((is_numeric($codigoProducto) && !is_bool($codigoProducto)) || $data["productoTipo"] == "noCodificado"){
                        if((Producto::corroboraExistencia(["codigo" => $codigoProducto])) || $data["productoTipo"] == "noCodificado"){
                            $productoEnStock = Compania::stockCorroboraExistencia($data["idProducto"], null, null, $data["productoTipo"]);
                            Session::iniciar();
                            if(is_bool($productoEnStock) && $productoEnStock){
                                $idProductoStock = Compania::stockGetId($data["idProducto"], null, null, $data["productoTipo"]);
                                if(is_numeric($idProductoStock) && $idProductoStock > 0){
                                    $query = DataBase::update("producto_stock", $data["tipo"]." = ".$data["cantidad"].", operador = '".$_SESSION["usuario"]->getId()."'", "id = '".$idProductoStock."' AND ".(($data["productoTipo"] == "codificado") ? "producto" : "productoNC")." = '".$data["idProducto"]."' AND sucursal = '".$_SESSION["usuario"]->getSucursal()."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                                    if($query){
                                        echo '<script>successAction("#producto-'.$data["idProducto"].' #'.$data["tipo"].'", () => { return compañiaStockContenidoData('.$data["idProducto"].', "'.$data["tipo"].'", "'.$data["productoTipo"].'"); }, "loader-ok")</script>';
                                    }else{
                                        Sistema::debug('error', 'compania.class.php - stockEditarContenido - Hubo un error al editar el contenido del stock. Ref.: '.$idProductoStock);
                                    }
                                }else{
                                    Sistema::debug('error', 'compania.class.php - stockEditarContenido - Hubo un error al recibir el identificador del stock del producto. Ref.: '.$idProductoStock);
                                }
                            }elseif(is_numeric($productoEnStock) && $productoEnStock == 0){
                                $query = DataBase::insert("producto_stock", (($data["productoTipo"] == "codificado") ? "producto" : "productoNC").",sucursal,compañia,".$data["tipo"].",operador", "'".$data["idProducto"]."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."','".$data["cantidad"]."','".$_SESSION["usuario"]->getId()."'");
                                if($query){
                                    echo '<script>successAction("#producto-'.$data["idProducto"].' #'.$data["tipo"].'", () => { return compañiaStockContenidoData('.$data["idProducto"].', "'.$data["tipo"].'", "'.$data["productoTipo"].'"); }, "loader-ok")</script>';
                                }else{
                                    Sistema::debug('error', 'compania.class.php - stockEditarContenido - Hubo un error al registrar '.$data["tipo"].' del producto. Ref.: '.$codigoProducto);
                                }
                            }else{
                                Sistema::debug('info', 'compania.class.php - stockEditarContenido - No se pudo comprobar la existencia de stock del producto. Ref.: '.$codigoProducto);
                                echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-info"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                            }
                        }else{
                            Sistema::debug('info', 'compania.class.php - stockEditarContenido - Producto inexistente. Ref.: '.$codigoProducto);
                            echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-info"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                        }
                    }else{ 
                        Sistema::debug('error', 'compania.class.php - stockEditarContenido - Código de producto incorrecto. Ref.: '.$codigoProducto);
                        echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - stockEditarContenido - Arreglo de datos incorrecto.');
                    echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockEditarContenido - Usuario no logueado.');
            }
        }

        public static function stockEditarContenidoFormulario($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) == 4){
                    ?>
                    <div id="producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-process" style="display: none"></div>
                    <form id="producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-form" action="./engine/compania/stock-editar-contenido.php" form="#producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-form" process="#producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-process"> 
                        <div class="form-group mb-0"> 
                            <div class="input-group">
                                <input class="form-control form-control-sm" type="number" id="cantidad" name="cantidad" min="0" max="32767" value="<?php echo ($data["cantidad"] > 0) ? $data["cantidad"] : "" ?>">
                                <input class="form-control form-control-sm d-none" type="text" id="tipo" name="tipo" value="<?php echo $data["tipo"] ?>" readonly>
                                <input class="form-control form-control-sm d-none" type="text" id="idProducto" name="idProducto" value="<?php echo $data["producto"] ?>" readonly>
                                <div class="input-group-append">
                                    <button type="button" onclick="compañiaStockEditarContenido(<?php echo $data['producto'] ?>,'<?php echo $data['tipo'] ?>','<?php echo $data['productoTipo'] ?>')" class="btn btn-sm btn-outline-success"><i class="fa fa-check"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <script>
                        $(document).ready(() => {
                            $("#producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-form #cantidad").focus();
                            $("#producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-form #cantidad").keypress((e) => {
                                var keycode = (e.keyCode ? e.keyCode : e.which);
                                if(keycode == '13'){
                                    compañiaStockEditarContenido(<?php echo $data['producto'] ?>,'<?php echo $data['tipo'] ?>','<?php echo $data['productoTipo'] ?>') 
                                }
                            });
                        })
                    </script>
                    <?php
                }else{
                    Sistema::debug('error', 'compania.class.php - stockEditarContenidoFormulario - Error en arreglo de datos.');
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockEditarContenidoFormulario - Usuario no logueado.');
            }
        }

        public static function stockRegistro($data, $alert = false, $codificado = true){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    $productoExiste = ($codificado) ? Producto::corroboraExistencia(["codigo" => $data["codigo"]]) : Producto::nocodifCorroboraExistencia(["codigo" => $data["codigo"], "idProducto" => $data["idProducto"]]);
                    if($productoExiste){
                        $query = DataBase::insert("producto_stock", (($codificado) ? "producto" : "productoNC").",sucursal,compañia,stock,minimo,maximo,precio,precioMayorista,precioKiosco,operador", "'".$data["idProducto"]."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."',".((is_numeric($data["stock"])) ? $data["stock"] : "NULL").",".((is_numeric($data["minimo"])) ? $data["minimo"] : "NULL").",".((is_numeric($data["maximo"])) ? $data["maximo"] : "NULL").",".((is_numeric($data["precio"])) ? $data["precio"] : "NULL").",".((is_numeric($data["precioMayorista"])) ? $data["precioMayorista"] : "NULL").",".((is_numeric($data["precioKiosco"])) ? $data["precioKiosco"] : "NULL").",'".$_SESSION["usuario"]->getId()."'");
                        if($query){
                            if($alert){
                                $mensaje['tipo'] = 'success';
                                $mensaje['cuerpo'] = 'Se registró el stock del producto satisfactoriamente.';
                                Alert::mensaje($mensaje);
                            }
                            return true;
                        }else{
                            if($alert){
                                $mensaje['tipo'] = 'danger';
                                $mensaje['cuerpo'] = 'Hubo un error al registrar el stock del producto. <b>Intente nuevamente o contacte al administrador.</b>';
                                Alert::mensaje($mensaje);
                            } 
                            Sistema::debug('error', 'compania.class.php - stockRegistro - Error al registrar el stock del producto. Ref.: '.DataBase::getError());
                        }
                    }else{
                        Sistema::debug('error', 'compania.class.php - stockRegistro - Producto inexistente. Ref.: '.$data["codigo"]);
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - stockRegistro - Error en arreglo de datos recibido. Ref.: '.count($data));
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockRegistro - Usuario no logueado.');
            }
            return false;
        }

        public static function productoNoCodifData($idCompañia = null){
            Session::iniciar();
            $idCompañia = (is_null($idCompañia)) ? $_SESSION["usuario"]->getCompañia() : $idCompañia;
            $query = DataBase::select("compañia_producto", "*", "compañia = '".$idCompañia."'", "ORDER BY nombre ASC");
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
            }
            return false;
        }

        public static function stockFormulario(){
            if(Sistema::usuarioLogueado()){
                $data = Compania::stockData();
                ?>
                <div class="mine-container">
                    <div class="d-flex justify-content-between">
                        <div class="titulo">Stock de productos de <?php echo mb_strtoupper(Compania::getNombre($_SESSION["usuario"]->getCompañia())) ?> - <?php echo Compania::sucursalGetNombre($_SESSION["usuario"]->getSucursal()) ?></div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-info" onclick="productoNoCodifRegistroFormulario()"><i class="fa fa-plus"></i> Agregar productos no codificados</button>
                            <button type="button" class="btn btn-info" onclick="compañiaStockRegistroProductoFormulario()"><i class="fa fa-plus"></i> Agregar productos al stock</button>
                        </div>
                    </div> 

                    <script> 
                        $(document).ready(function(){
                            $("#buscador-input").on("keyup", 
                                function(){
                                    $("#tabla-producto-inventario #loading").removeClass("d-none");
                                    $("#tabla-producto-inventario").find("tbody tr").css({display: "none"}); 
                                    $("#tabla-producto-inventario #not-found").addClass("d-none");
                                    $("#tabla-producto-inventario #go-find").addClass("d-none");
                                    setTimeout(() => {
                                        $("#tabla-producto-inventario #loading").addClass("d-none"); 
                                        var value = $(this).val().toLowerCase();
                                        if(value.length > 0){ 
                                            $("#tabla-producto-inventario tbody tr").filter(function (){ 
                                                $(this).toggle($(this).text().normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().indexOf(value.normalize("NFD").replace(/[\u0300-\u036f]/g, "")) > -1); 
                                            });
                                            let resultados = $('#tabla-producto-inventario').find('tbody tr:visible');
                                            console.log(resultados);
                                            if(resultados.length > 0){
                                                $("#tabla-producto-inventario #not-found").addClass("d-none");
                                            }else{
                                                $("#tabla-producto-inventario #not-found").removeClass("d-none");
                                            }
                                        }else{
                                            $("#tabla-producto-inventario").find("tbody tr").css({display: "none"}); 
                                            $("#tabla-producto-inventario #go-find").removeClass("d-none");
                                        } 
                                    }, 350);
                                }
                            );
                        }); 
                    </script>

                    <div class="">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="d-flex flex-column" style="height: fit-content; width: 75vw; text-align: left; position: sticky; top: 125px;">
                                <span id="titulo"><i class="fa fa-barcode"></i> Buscar producto <small class="text-muted font-weight-bold">(<?php echo count($data) ?> productos registrados)</small></span>
                                <input class="form-control form-control-lg w-100 align-self-center" type="text" placeholder="" autocomplete="off" id="buscador-input">
                            </div>
                        </div>
                    </div>

                    <div class="p-1 w-100 mh-50 overflow-auto"> 
                        <table id="tabla-producto-inventario" class="table table-hover">
                            <thead>
                                <tr>
                                    <th id="tag-codigo" scope="col">Código</th>
                                    <th id="tag-producto" scope="col">Producto</th>
                                    <th id="tag-stock" class="text-center" scope="col">Stock</th>
                                    <th id="tag-minimo" class="text-center" scope="col">S. Mínimo</th>
                                    <th id="tag-maximo" class="text-center" scope="col">S. Máximo</th>
                                    <th id="tag-preu" scope="col">Precio x U.</th>
                                    <th id="tag-prem" scope="col">Precio May.</th>
                                    <th id="tag-prek" scope="col">Precio Kiosco</th>
                                    <th id="tag-tipo" scope="col">Tipo</th>
                                    <th id="tag-categoria" scope="col">Categoría</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(is_array($data)){
                                        if(count($data) > 0){
                                            $producto = $_SESSION["lista"]["producto"];
                                            $productoTipo = $_SESSION["lista"]["producto"]["tipo"];
                                            $productoCategoria = $_SESSION["lista"]["producto"]["categoria"];
                                            $counter = 0;
                                            foreach($data AS $key => $value){
                                                if((is_numeric($value["producto"]) && $value["producto"] > 0)){
                                                    $idProducto =  $value["producto"];
                                                    $tipo = "codificado";
                                                }else{
                                                    $idProducto =  $value["productoNC"];
                                                    $tipo = "noCodificado";
                                                }
                                                ?>
                                                <tr style="display: none" id="producto-<?php echo $idProducto ?>" data-key="<?php echo $idProducto ?>" data-producto-tipo="<?php echo $tipo ?>">
                                                    <th scope="row"><?php echo $producto[$tipo][$idProducto]["codigoBarra"] ?></th>
                                                    <td id="nombre" data-value="<?php echo $producto[$tipo][$idProducto]["nombre"] ?>"><button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn"><?php echo $producto[$tipo][$idProducto]["nombre"] ?></span> <i class="fa fa-pencil"></i></button></td>
                                                    <td id="stock" data-value="<?php echo (isset($value["stock"]) && is_numeric($value["stock"])) ? $value["stock"] : 0 ?>" class="text-center"><?php echo (isset($value["sucursal"]) && $_SESSION["usuario"]->getSucursal() == $value["sucursal"]) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$value["stock"].'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i> stock inicial</a>" ?></td>
                                                    <td id="minimo" data-value="<?php echo (isset($value["minimo"]) && is_numeric($value["minimo"])) ? $value["minimo"] : 0 ?>" class="text-center"><?php echo (isset($value["minimo"]) && is_numeric($value["minimo"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$value["minimo"].'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="maximo" data-value="<?php echo (isset($value["maximo"]) && is_numeric($value["maximo"])) ? $value["maximo"] : 0 ?>" class="text-center"><?php echo (isset($value["maximo"]) && is_numeric($value["maximo"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$value["maximo"].'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="precio" data-value="<?php echo (isset($value["precio"]) && is_numeric($value["precio"])) ? $value["precio"] : "$0" ?>" class="text-center"><?php echo (isset($value["precio"]) && is_numeric($value["precio"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$'.$value["precio"].'</span> <i class="fa fa-pencil"></i></button>' : '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$0</span> <i class="fa fa-pencil"></i></button>' ?></td>
                                                    <td id="precioMayorista" data-value="<?php echo (isset($value["precioMayorista"]) && is_numeric($value["precioMayorista"])) ? $value["precioMayorista"] : "$0" ?>" class="text-center"><?php echo (isset($value["precioMayorista"]) && is_numeric($value["precioMayorista"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$'.$value["precioMayorista"].'</span> <i class="fa fa-pencil"></i></button>' : '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$0</span> <i class="fa fa-pencil"></i></button>' ?></td>
                                                    <td id="precioKiosco" data-value="<?php echo (isset($value["precioKiosco"]) && is_numeric($value["precioKiosco"])) ? $value["precioKiosco"] : "$0" ?>" class="text-center"><?php echo (isset($value["precioKiosco"]) && is_numeric($value["precioKiosco"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$'.$value["precioKiosco"].'</span> <i class="fa fa-pencil"></i></button>' : '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$0</span> <i class="fa fa-pencil"></i></button>' ?></td>
                                                    <td><?php echo $productoTipo[$producto[$tipo][$idProducto]["tipo"]]; ?></td>
                                                    <td><?php echo $productoCategoria[$producto[$tipo][$idProducto]["categoria"]] ?></td>
                                                </tr>
                                                <?php
                                            } 
                                        }else{
                                            ?> 
                                            <tr>
                                                <td colspan="10" class="text-center">
                                                    No se encontraron productos registrados en la compañia. Para agregar un nuevo producto clickee en el siguiente <a href="#/" onclick="compañiaStockRegistroProductoFormulario()">link</a>.
                                                </td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
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
                                        if(is_bool($data) && !$data){
                                            Sistema::debug('error', 'compania.class.php - stockFormulario - Data boolean FALSE.');
                                        }else{
                                            Sistema::debug('error', 'compania.class.php - stockFormulario - Error desconocido.');
                                        }
                                        ?> 
                                        <tr>
                                            <td colspan="10" class="text-center">
                                                Hubo un error al encontrar los productos de la compañía. <b>Intente nuevamente o contacte al administrador.</b>
                                            </td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                            <td class="d-none"></td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr id="loading">
                                    <td colspan="10">
                                        <?php echo Sistema::loading(); ?>
                                    </td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                </tr>
                                <tr id="not-found">
                                    <td colspan="10" class="text-center">
                                        Producto no encontrado en el stock. Intente registrando el producto desde nuestra base de productos <a href="#/" onclick="compañiaStockRegistroProductoFormulario()">aquí</a>.
                                    </td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                </tr>
                                <tr id="go-find">
                                    <td colspan="10" class="text-center">
                                        Ingrese datos para buscar el producto en stock. <small class="text-muted">(código de barra, nombre, marca, etc.)</small>
                                    </td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                    <td class="d-none"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <script> 
                        $(document).ready(function() {
                            $('td a').on('click', (e) => {
                                let dataKey = e.currentTarget.parentNode.parentNode.getAttribute("data-key");
                                let productoTipo = e.currentTarget.parentNode.parentNode.getAttribute("data-producto-tipo");
                                let id = e.currentTarget.parentNode.getAttribute("id");
                                let dataValue = e.currentTarget.parentNode.getAttribute("data-value");
                                if(id === "nombre"){
                                    alert("Esta característica será habilitada en breve.");
                                    return;
                                    productoEditarContenidoFormulario(dataKey,id,dataValue);
                                }else{
                                    productoInventarioEditarContenidoFormulario(dataKey,id,dataValue,productoTipo);
                                }
                            });
                            $('td>button').on('click', (e) => {
                                let dataKey = e.currentTarget.parentNode.parentNode.getAttribute("data-key");
                                let productoTipo = e.currentTarget.parentNode.parentNode.getAttribute("data-producto-tipo");
                                let id = e.currentTarget.parentNode.getAttribute("id");
                                let dataValue = e.currentTarget.parentNode.getAttribute("data-value");
                                if(id === "nombre"){
                                    alert("Esta característica será habilitada en breve.");
                                    return;
                                    productoEditarContenidoFormulario(dataKey,id,dataValue);
                                }else{
                                    productoInventarioEditarContenidoFormulario(dataKey,id,dataValue,productoTipo);
                                }
                            });
                            tippy('td a', {
                                content: 'Click para agregar un nuevo valor.',
                                delay: [150,150],
                                animation: 'fade'
                            });
                            tippy('td button', {
                                content: 'Click para modificar el valor.',
                                delay: [150,150],
                                animation: 'fade'
                            });
                            tippy('#tag-codigo', {
                                content: '<i class="fa fa-barcode"></i> Código de barra del producto.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-producto', {
                                content: 'Nombre y descripción del artículo.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-stock', {
                                content: 'Cantidad de artículo en stock.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-minimo', {
                                content: 'Stock mínimo es un parámetro definido para el sistema, con el cual se le dará una notificación cuando este artículo alcance valores inferios al guardado.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-maximo', {
                                content: 'Stock máximo al contrario de "Stock Mínimo", dará una notificación cuando este artículo supere al valor guardado.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-preu', {
                                content: 'Precio por unidad del artículo.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-prem', {
                                content: 'Precio al por mayor del artículo.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-prek', {
                                content: 'Precio de kiosco del artículo.',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-tipo', {
                                content: 'Tipo de producto',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            tippy('#tag-categoria', {
                                content: 'Categoría de producto',
                                delay: [150,150],
                                animation: 'fade',
                                allowHTML: true
                            });
                            $("#buscador-input").focus();
                            $("#tabla-producto-inventario #loading").addClass("d-none");
                            $("#tabla-producto-inventario #not-found").addClass("d-none");
                            $("#tabla-producto-inventario #go-find").removeClass("d-none");
                        } ); 
                    </script>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'compania.class.php - stockFormulario - Usuario no logueado.');
            }
        }

        public static function stockData($idCompañia = null, $idSucursal = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("producto_stock", "*", "compañia = '".((is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia())."' ".((is_numeric($idSucursal) && $idSucursal > 0) ? " AND sucursal = '".$idSucursal."'" : "" ), "");
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
                    Sistema::debug('error', 'compania.class.php - stockData - Hubo un error al buscar la información del stock de la compañía.');
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockData - Usuario no logueado.');
            }
            return false;
        }

        public static function sucursalGetNombre($idSucursal){
            if(isset($idSucursal) && is_numeric($idSucursal) && $idSucursal > 0){
                $query = DataBase::select("compañia_sucursal", "nombre", "id = '".$idSucursal."'", "");
                if($query){
                    if(DataBase::getNumRows($query) == 1){
                        $dataQuery = DataBase::getArray($query);
                        return $dataQuery["nombre"];
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

        public static function getCompania($idCompañia = null){ 
            if(Sistema::usuarioLogueado()){
                $data = Lista::compañia($idCompañia);
                if(is_array($data) && count($data) > 0){
                    return $data;
                }else{
                    Sistema::debug('error', 'compania.class.php - getCompania - Error al recibir la información de la lista de compañía.');
                }
            }else{
                Sistema::debug('error', 'compania.class.php - getCompania - Usuario no logueado.');
            }
            return false;
        } 

        public static function buscarFormulario($callback){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $compañia = $_SESSION["lista"]["compañia"];
                ?>
                <div> 
                    <div class="titulo">Búsqueda de Compañía</div>
                    <div id="compania-buscar-process"></div>
                    <form id="compania-buscar-form"> 
                        <div class="form-group">
                            <label for="compania"><i class="fa fa-list-alt"></i> Seleccione Compañía</label>
                            <div class="input-group">
                                <select class="form-control" id="compania" name="compania">
                                    <option value=""> -- </option>
                                    <?php
                                        foreach($compañia AS $key => $value){
                                            echo '<option value="'.$key.'">'.$value["nombre"].'</option>';
                                        }
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" onclick="<?php echo $callback ?>" class="btn btn-success"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <script>
                        $("#compania").change(() => {
                            <?php echo $callback ?>
                        });
                        tailSelectSet("#compania", true, []);
                    </script>
                </div>
                <?php 
            }else{
                Sistema::debug('error', 'compania.class.php - buscarFormulario - Usuario no logueado.');
            }
        }

        public static function getNombre($idCompañia){
            if(isset($idCompañia) && is_numeric($idCompañia) && $idCompañia > 0){
                $query = DataBase::select("compañia", "nombre", "id = '".$idCompañia."'", "");
                if($query){
                    if(DataBase::getNumRows($query) == 1){
                        $dataQuery = DataBase::getArray($query);
                        return $dataQuery["nombre"];
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
    }
?>