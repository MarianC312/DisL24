<?php
    class Compania{ 
        public static function productoHistorialFormulario($codigoBarra, $producto){
            if(Sistema::usuarioLogueado()){
                if(isset($codigoBarra) && !is_null($codigoBarra) && strlen($codigoBarra) > 0){
                    Session::iniciar();
                    $productoHistorialResponse = Producto::historial($producto["data"]["id"], ((strpos($codigoBarra, "PFC") !== false) ? "noCodificado" : "codificado"));
                    if($productoHistorialResponse["status"] === true){ 
                        ?>
                        <table id="producto-historial" class="table table-hover w-100">
                            <thead>
                                <tr>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Código</th>
                                    <th class="fit" scope="col">Stock</th>
                                    <th class="fit" scope="col">Precio</th>
                                    <th class="fit" scope="col">Precio Mayorista</th>
                                    <th class="fit" scope="col">Precio Kiosco</th>
                                    <th scope="col">Operador</th>
                                    <th class="fit" scope="col">Fecha y Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td scope="row"><?php echo $producto["data"]["nombre"] ?></td>
                                    <td><?php echo $producto["data"]["codigoBarra"] ?></td>
                                    <td><?php echo $producto["stock"]["stock"] ?></td>
                                    <td>$ <?php echo $producto["stock"]["precio"] ?></td>
                                    <td>$ <?php echo $producto["stock"]["precioMayorista"] ?></td>
                                    <td>$ <?php echo $producto["stock"]["precioKiosco"] ?></td>
                                    <td><?php echo $_SESSION["lista"]["operador"][$producto["stock"]["operador"]]["nombre"] ?></td>
                                    <td><?php echo date("d/m/Y, H:i A", strtotime($producto["stock"]["fechaModificacion"])) ?></td>
                                </tr>
                                <?php
                                    if(count($productoHistorialResponse["data"]["array"]) > 0){
                                        foreach($productoHistorialResponse["data"]["array"] AS $key => $value){
                                            ?>
                                            <tr>
                                                <td scope="row"><?php echo $producto["data"]["nombre"] ?></td>
                                                <td><?php echo $producto["data"]["codigoBarra"] ?></td>
                                                <td class="fit"><?php echo $value["stock"] ?></td>
                                                <td class="fit">$ <?php echo $value["precio"] ?></td>
                                                <td class="fit">$ <?php echo $value["precioMayorista"] ?></td>
                                                <td class="fit">$ <?php echo $value["precioKiosco"] ?></td>
                                                <td><?php echo $_SESSION["lista"]["operador"][$value["operador"]]["nombre"] ?></td>
                                                <td class="fit"><?php echo date("d/m/Y, H:i A", strtotime($value["fechaModificacion"])) ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }else{

                                    }
                                ?>
                            </tbody>
                        </table>
                        <script>
                            
                            dataTableSet("#producto-historial"); 
                                
                        </script>
                        <?php
                    }else{
                        Sistema::alerta("Error", $productoHistorialResponse["mensajeUser"]."<br><br>".$productoHistorialResponse["mensajeAdmin"]);
                    }
                }else{
                    Sistema::alerta("Error", "Ocurrió un error con la información recibida. <b>Intente nuevamente o contacte al administrador.</b>");
                }
            }else{
                Sistema::debug('Error', 'Compania > productoHistorialFormulario - Usuario no logueado.');
            }
        }

        public static function productoHistorial(){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                ?>
                <div class="mine-container">
                    <div class="d-flex justify-content-between">
                        <div class="titulo">Historial de productos - <?php echo mb_strtoupper(Compania::getNombre($_SESSION["usuario"]->getCompañia())) ?></div>
                    </div> 

                    <div class="">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="d-flex flex-column" style="height: fit-content; width: 75vw; text-align: left; position: sticky; top: 125px;">
                                <span id="titulo"><i class="fa fa-barcode"></i> Buscar producto</span>
                                <input class="form-control form-control-lg w-100 align-self-center" type="text" placeholder="" autocomplete="off" id="buscador-input">
                            </div>
                        </div>
                    </div>

                    <div id="stock-producto"></div> 

                    <script> 
                        $(document).ready(function() {
                            tippy('td a', {
                                content: 'Click para agregar un nuevo valor.',
                                delay: [150,150],
                                animation: 'fade'
                            });
                            $("#buscador-input").focus();
                            $("#buscador-input").on("keydown", (e) => {
                                let keycode = (e.keyCode ? e.keyCode : e.which);
                                let barcode = $("#buscador-input").val();
                                let strokes = [96,97,98,99,100,101,102,103,104,105];
                                switch(keycode){
                                    case 45:
                                        $("#buscador-input").val("PFC-2-").focus();    
                                    break;
                                    case 13:
                                        if(barcode.length > 0){
                                            compañiaProductoHistorialFormulario("#stock-producto", barcode);
                                        }else{
                                            ventanaAlertaFlotante("Advertencia", "Debés ingresar algún valor para buscar...", $("#buscador-input").focus());
                                        }
                                    break;
                                    case 17:
                                        e.preventDefault();
                                        break;
                                    case 46: 
                                        e.preventDefault();
                                        $("#buscador-input").val("").focus();
                                    break;
                                } 
                            });
                            $("#tabla-producto-inventario #loading").addClass("d-none");
                            $("#tabla-producto-inventario #not-found").addClass("d-none");
                            $("#tabla-producto-inventario #go-find").removeClass("d-none");
                        } ); 
                    </script>
                </div>
                <?php
            }else{
                Sistema::debug('Error', 'Compania > productoHistorial - Usuario no logueado.');
            }
        }

        public static function consultaProductoNuevoActualizado($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    if(!isset($_SESSION["productoActualizado"]) || $_SESSION["productoActualizado"] == null || count($_SESSION["productoActualizado"]) == 0){ 
                        $_SESSION["productoActualizado"] = [
                            "data" => array_merge(Lista::producto($_SESSION["usuario"]->getLastReloadBaseProducto()), Lista::productoNoCodificado($_SESSION["usuario"]->getLastReloadBaseProducto())),
                            "stock" => Compania::stockData(null, null, $_SESSION["usuario"]->getLastReloadBaseProducto())
                        ];
                    }
                    if((is_array($_SESSION["productoActualizado"]["data"]) && count($_SESSION["productoActualizado"]["data"]) > 0) || (is_array($_SESSION["productoActualizado"]["stock"]) && count($_SESSION["productoActualizado"]["stock"]) > 0)){ 
                        if($data["force"] === true || $data["force"] === "true" || $data["venta"] === false || $data["venta"] === "false"){
                            Sistema::alertaSmall("Actualizando base de productos <span class='ml-2 loader-circle-1'></span>");
                            if((is_array($_SESSION["productoActualizado"]["data"]) && count($_SESSION["productoActualizado"]["data"]) > 0)){
                                foreach($_SESSION["productoActualizado"]["data"] AS $key => $value){
                                    if(array_key_exists($value["id"], $_SESSION["lista"]["producto"][($value["tipo"] == 5) ? "noCodificado" : "codificado"])){
                                        $producto = $_SESSION["lista"]["producto"][($value["tipo"] == 5) ? "noCodificado" : "codificado"][$value["id"]];
                                        $codigoBarra = (($value["tipo"] == 5) ? "PFC-".$_SESSION["usuario"]->getCompañia()."-" : "").$producto["codigoBarra"]; 
                                        $_SESSION["lista"]["producto"][($value["tipo"] == 5) ? "noCodificado" : "codificado"][$value["id"]]["nombre"] = $value["nombre"];
                                        $_SESSION["lista"]["producto"][($value["tipo"] == 5) ? "noCodificado" : "codificado"][$value["id"]]["fechaUpdate"] = $value["fechaUpdate"];
                                        ?>
                                        <script>
                                            var producto = document.querySelector('#right-content-producto-data #companiaProductoLista [data-producto-codigoBarra="<?php echo $codigoBarra ?>"]') 
                                            if(producto !== null){
                                                producto.dataset["productoNombre"] = '<?php echo $value["nombre"] ?>';
                                                producto.dataset["productoFechaupdate"] = '<?php echo $value["fechaUpdate"] ?>';
                                            }else{
                                                ventanaAlertaFlotante("Error", "Ocurrió un error al actualizar la lista física de productos. <br>Esto no es un error mayor, antes de comenzar a registrar ventas recargá la web para actualizar la lista física.");
                                            }
                                        </script>
                                        <?php
                                    }
                                }
                            }
                            if((is_array($_SESSION["productoActualizado"]["stock"]) && count($_SESSION["productoActualizado"]["stock"]) > 0)){
                                foreach($_SESSION["productoActualizado"]["stock"] AS $key => $value){
                                    if(is_numeric($value["producto"]) && $value["producto"] > 0){
                                        $tipo = "codificado";
                                        $idProducto = $value["producto"];
                                    }else{
                                        $tipo = "noCodificado";
                                        $idProducto = $value["productoNC"];
                                    }
                                    if(array_key_exists($idProducto, $_SESSION["lista"]["producto"][$tipo])){
                                        $producto = $_SESSION["lista"]["producto"][$tipo][$idProducto];
                                        $codigoBarra = (($tipo == "noCodificado") ? "PFC-".$_SESSION["usuario"]->getCompañia()."-" : "").$producto["codigoBarra"]; 
                                        ?>
                                        <script>
                                            var producto = document.querySelector('#right-content-producto-data #companiaProductoLista [data-producto-codigoBarra="<?php echo $codigoBarra ?>"]') 
                                            if(producto !== null){
                                                producto.dataset["stockStock"] = '<?php echo $value["stock"] ?>';
                                                producto.dataset["stockPrecio"] = '<?php echo $value["precio"] ?>';
                                                producto.dataset["stockPreciomayorista"] = '<?php echo $value["precioMayorista"] ?>';
                                                producto.dataset["stockPreciokiosco"] = '<?php echo $value["precioKiosco"] ?>';
                                                producto.dataset["stockFechamodificacion"] = '<?php echo $value["fechaModificacion"] ?>';
                                            }else{
                                                ventanaAlertaFlotante("Error", "Ocurrió un error al actualizar la lista física de productos. <br>Esto no es un error mayor, antes de comenzar a registrar ventas recargá la web para actualizar la lista física.");
                                            }
                                        </script>
                                        <?php
                                    }else{
                                        Sistema::alerta("Advertencia", "Encontramos productos para actualizar en tu base física, pero es necesario que recargues la web con F5. <br><br>Enviá una captura al administrador para corregir este inconveniente.");
                                    }
                                }
                            }
                            unset($_SESSION["productoActualizado"]["data"]);
                            unset($_SESSION["productoActualizado"]["stock"]);
                            $_SESSION["usuario"]->setLastReloadBaseProducto();
                            echo '<script>
                                setTimeout(() => { 
                                    $("#menu-stock-recarga-badge").html("0").addClass("d-none");
                                }, 350);
                            </script>';
                        }else{ 
                            $cantidad = count($_SESSION["productoActualizado"]["data"]) + count($_SESSION["productoActualizado"]["stock"]);
                            ?>
                            <script>
                                console.log("En venta");
                                var cantidad = parseInt(<?php echo $cantidad ?>);
                                console.log(cantidad + " productos por actualizar.");
                                setTimeout(() => { 
                                    if(cantidad > 0){
                                        $("#menu-stock-recarga-badge").html(cantidad).removeClass("d-none");
                                    }else{
                                        $("#menu-stock-recarga-badge").html(cantidad).addClass("d-none");
                                    }
                                }, 350);
                            </script>
                            <?php
                            if($data["alerta"] === true || $data["alerta"] === "true") Sistema::alerta("Advertencia", "Tenés ".$cantidad." productos por actualizar pero es necesario que termines o cierres la venta antes de continuar.");
                        }
                    }else{
                        unset($_SESSION["productoActualizado"]["data"]);
                        unset($_SESSION["productoActualizado"]["stock"]);
                        echo '<script>console.log("Sin productos para actualizar.")</script>';
                        if($data["alerta"] === true || $data["alerta"] === "true") Sistema::alerta("Advertencia", "No tenes productos o stock para actualizar.");
                    }
                }else{
                    Sistema::alerta("Error", "Al recibir la información necesaria para consultar la actualización del stock. <br>Contacte al administrador...");
                }
            }else{
                Sistema::debug('Error', 'Compania > consultaProductoNuevoActualizado - Usuario no logueado.');
            }
        }

        public static function stockEditar($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    if($data["idProducto"] === $data["idProducto2"]){ 
                        if($data["idStock"] === $data["idStock2"]){
                            Session::iniciar();
                            if(array_key_exists($data["idProducto"], $_SESSION["lista"]["producto"][$data["tipo"]])){
                                $producto = $_SESSION["lista"]["producto"][$data["tipo"]][$data["idProducto"]];
                                if(($data["tipo"] == "codificado" && $producto["codigoBarra"] === $data["codigoBarra"]) || ($data["tipo"] == "noCodificado" && "PFC-".$_SESSION["usuario"]->getCompañia()."-".$producto["codigoBarra"] === $data["codigoBarra"])){
                                    if(array_key_exists($data["idStock"], $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"])){
                                        $stock = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"][$data["idStock"]];
                                        if(intval($data["idProducto"]) === intval($stock[($data["tipo"] == "codificado") ? "producto" : "productoNC"])){
                                            if($data["nombre"] != $producto["nombre"]){
                                                switch($data["tipo"]){
                                                    case "codificado":
                                                        $query = DataBase::update("producto", "nombre = '".$data["nombre"]."', operador = '".$_SESSION["usuario"]->getId()."', revision = 1", "id = '".$data["idProducto"]."' && codigoBarra = '".$data["codigoBarra"]."' && estado = 1");
                                                        break;
                                                    case "noCodificado":
                                                        $codigo = explode("-", $data["codigoBarra"]);
                                                        $query = DataBase::update("compañia_producto", "nombre = '".$data["nombre"]."', operador = '".$_SESSION["usuario"]->getId()."'", "id = '".$data["idProducto"]."' && codigoBarra = '".$codigo[count($codigo) - 1]."' && compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                                                        break;
                                                }
                                                if($query){
                                                    $_SESSION["lista"]["producto"][$data["tipo"]][$data["idProducto"]]["nombre"] = $data["nombre"];
                                                    $_SESSION["lista"]["producto"][$data["tipo"]][$data["idProducto"]]["fechaUpdate"] = Date::current();
                                                    if($data["stock"] == $stock["stock"] && $data["precio"] == $stock["precio"] && $data["precioMayorista"] == $stock["precioMayorista"] && $data["precioKiosco"] == $stock["precioKiosco"]){
                                                        ?>
                                                        <script>
                                                            var producto = document.querySelector('#right-content-producto-data #companiaProductoLista [data-producto-codigoBarra="<?php echo $data["codigoBarra"] ?>"]') 
                                                            if(producto !== null){
                                                                producto.dataset["productoNombre"] = '<?php echo $data["nombre"] ?>';
                                                                producto.dataset["productoFechaupdate"] = '<?php echo Date::current() ?>';
                                                            }else{
                                                                ventanaAlertaFlotante("Error", "Ocurrió un error al actualizar la lista física de productos. <br>Esto no es un error mayor, antes de comenzar a registrar ventas recargá la web para actualizar la lista física.");
                                                            }
                                                        </script>
                                                        <?php
                                                        Sistema::alerta("Satisfactorio", "Se actualizó toda la información del producto satisfactoriamente.", "$('#buscador-input').val('').focus()"); 
                                                    }
                                                }else{
                                                    Sistema::alerta("Error", "Ocurrió un error al modificar el nombre del producto. Intente nuevamente o contacte al administrador. <br><br> Ref.: ".DataBase::getError());
                                                }
                                            }
                                            if($data["stock"] != $stock["stock"] || $data["precio"] != $stock["precio"] || $data["precioMayorista"] != $stock["precioMayorista"] || $data["precioKiosco"] != $stock["precioKiosco"]){
                                                $productoStockColumna = ($data["tipo"] == "codificado") ? "producto" : "productoNC";
                                                $query = DataBase::update("producto_stock", "stock = '".$data["stock"]."', precio = '".$data["precio"]."', precioMayorista = '".$data["precioMayorista"]."', precioKiosco = '".$data["precioKiosco"]."'", "id = '".$data["idStock"]."' && ".$productoStockColumna." = '".$data["idProducto"]."' && compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                                                if($query){
                                                    $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"][$data["idStock"]]["stock"] = $data["stock"];
                                                    $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"][$data["idStock"]]["precio"] = $data["precio"];
                                                    $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"][$data["idStock"]]["precioMayorista"] = $data["precioMayorista"];
                                                    $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"][$data["idStock"]]["precioKiosco"] = $data["precioKiosco"];
                                                    $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"][$data["idStock"]]["fechaModificacion"] = Date::current();

                                                    ?>
                                                    <script>
                                                        var producto = document.querySelector('#right-content-producto-data #companiaProductoLista [data-producto-codigoBarra="<?php echo $data["codigoBarra"] ?>"]') 
                                                        if(producto !== null){
                                                            producto.dataset["productoNombre"] = '<?php echo $data["nombre"] ?>';
                                                            producto.dataset["productoFechaupdate"] = '<?php echo Date::current() ?>';
                                                            producto.dataset["stockStock"] = '<?php echo $data["stock"] ?>';
                                                            producto.dataset["stockPrecio"] = '<?php echo $data["precio"] ?>';
                                                            producto.dataset["stockPreciomayorista"] = '<?php echo $data["precioMayorista"] ?>';
                                                            producto.dataset["stockPreciokiosco"] = '<?php echo $data["precioKiosco"] ?>';
                                                            producto.dataset["stockFechamodificacion"] = '<?php echo Date::current() ?>';
                                                        }else{
                                                            ventanaAlertaFlotante("Error", "Ocurrió un error al actualizar la lista física de productos. <br>Esto no es un error mayor, antes de comenzar a registrar ventas recargá la web para actualizar la lista física.");
                                                        }
                                                    </script>
                                                    <?php 
                                                    Sistema::alerta("Satisfactorio", "Se actualizó toda la información del producto y stock satisfactoriamente.", "$('#buscador-input').val('').focus()");
                                                }else{
                                                    Sistema::alerta("Error", "Ocurrió un error al modificar la información de stock del producto. Intente nuevamente o contacte al administrador. <br><br> Ref.: ".DataBase::getError());
                                                }
                                            }else{
                                                if($data["nombre"] == $producto["nombre"]){
                                                    Sistema::alerta("Advertencia", "Cambia al menos algún dato del formulario para realizar la actualización en tus productos y stock.", "$('".$data["process"]."').hide(150); $('".$data["form"]."').show(150);");
                                                }
                                            }
                                        }else{
                                            Sistema::alerta("Error", "El identificador del producto recibido no concuerda con el de stock de la base de la compañía. Contacte al administrador. <br><br>Ref.: '".$data["idProducto"]."' | '".$stock[($data["tipo"] == "codificado") ? "producto" : "productoNC"]."'", "$('".$data["process"]."').hide(150); $('".$data["form"]."').show(150);");
                                        }
                                    }else{
                                        Sistema::alerta("Error", "No se encontró el producto en la base de stock de productos de la compañía. Intente nuevamente o contacte al administrador.", "$('".$data["process"]."').hide(150); $('".$data["form"]."').show(150);");
                                    }
                                }else{
                                    Sistema::alerta("Error", "No se pudieron comprobar los datos del producto en la base de productos existente. Contacte al administrador...", "$('".$data["process"]."').hide(150); $('".$data["form"]."').show(150);");
                                }
                            }else{
                                Sistema::alerta("Error", "No se encontró el producto en la base de productos de la compañía. Intente nuevamente o contacte al administrador.", "$('".$data["process"]."').hide(150); $('".$data["form"]."').show(150);");
                            }
                        }else{
                            Sistema::alerta("Advertencia", "No se pudo comprobar el identificador de stock del producto. Intente nuevamente...", "$('".$data["process"]."').hide(150); $('".$data["form"]."').show(150);");
                        }
                    }else{
                        Sistema::alerta("Advertencia", "No se pudo comprobar el identificador del producto. Intente nuevamente...", "$('".$data["process"]."').hide(150); $('".$data["form"]."').show(150);");
                    }
                }else{
                    Sistema::alerta("Error", "Ocurrió un error con la información recibida. Intente nuevamente, si el problema persiste, contacte al administrador...", "$('".$data["process"]."').hide(150); $('".$data["form"]."').show(150);");
                }
            }else{
                Sistema::debug('error', 'compania.class.php - stockEditar - Usuario no logueado.');
            }
        }

        public static function facturaImpagaData($compañia = null){
            if(Sistema::usuarioLogueado()){
                $idCompañia = (is_numeric($compañia) && $compañia > 0) ? $compañia : $_SESSION["usuario"]->getCompañia();
                $query = DataBase::select("sistema_compañia_facturacion", "*", "estado = 1 AND fechaPago IS NULL AND compañia = '".$idCompañia."'", "ORDER BY fechaCarga DESC");
                if($query){
                    $data = [];
                    if(DataBase::getNumRows($query) > 0){
                        while($dataQuery = DataBase::getArray($query)){
                            $data = $dataQuery;
                        }
                        foreach($data AS $key => $value){
                            if(is_int($key)){
                                unset($data[$key]);
                            } 
                        }
                    }
                    return $data;
                }else{
                    Sistema::debug('error', 'compania.class.php - facturaImpagaData - Error al consultar información de facturación de la compañía. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('error', 'compania.class.php - facturaImpagaData - Usuario no logueado.');
            }
            return false;
        }

        public static function sucursalPedidoGetData($idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $sucursal = (is_numeric($idSucursal)) ? $idSucursal : $_SESSION["usuario"]->getSucursal();
                $compañia = (is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia();
                $query = DataBase::select("compañia_sucursal_venta", "*", "pedido = 1 AND sucursal = '".$sucursal."' AND compañia = '".$compañia."'", "ORDER BY CASE WHEN fechaPago IS NULL AND estado = 1 THEN 0 ELSE 1 END, fechaCarga DESC");
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
                    Sistema::debug('error', 'compania.class.php - sucursalPedidoGetData - Error al encontrar los pedidos de la sucursal. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('error', 'compania.class.php - sucursalPedidoGetData - Usuario no logueado.');
            }
            return false;
        }

        public static function sucursalPedidoCarritoFormularioRegistrar($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Venta::registrar($data);
                }else{
                    Sistema::debug('error', 'compania.class.php - sucursalPedidoCarritoFormularioRegistrar - Error en información recibida. Ref.: '.count($data));
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con la información recibida. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - sucursalPedidoCarritoFormularioRegistrar - Usuario no logueado.');
            }
        }

        public static function sucursalPedidoFormularioProductoFiltrar($data, $idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    $sucursal = (is_numeric($idSucursal)) ? $idSucursal : $_SESSION["usuario"]->getSucursal();
                    $compañia = (is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia(); 
                    $productoCodificado = Producto::buscadorData(["filtroOpcion" => 1, "tag" => $data["tag"]], 100); 
                    $productoNoCodificado = Compania::productoNoCodifData(null, null, ["filtroOpcion" => 1, "tag" => $data["tag"]], 100);
                    if(is_array($productoCodificado)){
                        if(!is_array($productoNoCodificado)){
                            $mensaje['tipo'] = 'warning';
                            $mensaje['cuerpo'] = 'Hubo un error al recibir la información de los productos de la compañía. No se mostrarán los productos propios como cajones de coca cola o cerveza, o cualquier otro producto cargado a la base de la compañía.';
                            Alert::mensaje($mensaje);
                        }
                        $productoData = array_merge($productoCodificado, $productoNoCodificado);
                        $stock = Compania::stockData();
                        $stockProductoCodificado = [];
                        $stockProductoNoCodificado = [];
                        foreach($stock AS $key => $value){
                            if(is_numeric($value["producto"])){
                                array_push($stockProductoCodificado, $value["producto"]);
                            }elseif(is_numeric($value["productoNC"])){
                                array_push($stockProductoNoCodificado, $value["productoNC"]);
                            }
                        }
                        if(is_array($productoData)){
                            ?>
                            <table id="tabla-producto-filtrado" class="table table-hover table-responsive">
                                <thead>
                                    <tr>
                                        <th scope="col" style="white-space: nowrap !important;">Descripción de Producto<br><small class="text-muted">[Código de barra]</small></th>
                                        <th class="text-right">Precio</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        if(count($productoData) > 0){
                                            foreach($productoData AS $key => $value){
                                                $enStock = (Sistema::in_array_r($value["id"], $stockProductoCodificado)) ? true : false; //resolver búsqueda de prod en stock
                                                if($enStock && $value["tipo"] != 5){
                                                    $stockKey = "";
                                                    foreach($stock AS $iKey => $iValue){
                                                        if($value["id"] == $iValue["producto"]){
                                                            $stockKey = $iKey;
                                                            break;
                                                        }
                                                    }
                                                    $prodTipo = "codificado";
                                                    $prodStock = $stock[$stockKey]["stock"];
                                                    $prodPrecio = $stock[$stockKey]["precio"];
                                                    $prodPrecioMayorista = $stock[$stockKey]["precioMayorista"];
                                                    $prodPrecioKiosco = $stock[$stockKey]["precioKiosco"];
                                                }else{
                                                    $enStock = (Sistema::in_array_r($value["id"], $stockProductoNoCodificado)) ? true : false; 
                                                    if($enStock && $value["tipo"] == 5){
                                                        $stockKey = "";
                                                        foreach($stock AS $iKey => $iValue){
                                                            if($value["id"] == $iValue["productoNC"]){
                                                                $stockKey = $iKey;
                                                                break;
                                                            }
                                                        }
                                                        $prodTipo = "noCodificado";
                                                        $prodStock = $stock[$stockKey]["stock"];
                                                        $prodPrecio = $stock[$stockKey]["precio"];
                                                        $prodPrecioMayorista = $stock[$stockKey]["precioMayorista"];
                                                        $prodPrecioKiosco = $stock[$stockKey]["precioKiosco"];
                                                    }else{
                                                        $prodStock = "Sin stock";
                                                        $prodPrecio = null;
                                                        $prodPrecioMayorista = null;
                                                        $prodPrecioKiosco = null;
                                                    }
                                                    
                                                }
                                                switch($data["precioTipo"]){
                                                    case 1:
                                                        $order[1] = "order-1";
                                                        $order[2] = "order-2";
                                                        $order[3] = "order-3";
                                                        $precio = $prodPrecio;
                                                        break;
                                                    case 2:
                                                        $order[1] = "order-2";
                                                        $order[2] = "order-1";
                                                        $order[3] = "order-3";
                                                        $precio = $prodPrecioMayorista;
                                                        break;
                                                    case 3: 
                                                        $order[1] = "order-2";
                                                        $order[2] = "order-3";
                                                        $order[3] = "order-1";
                                                        $precio = $prodPrecioKiosco;
                                                        break;
                                                }
                                                ?>
                                                <tr id="producto-stock-<?php echo $stockKey ?>" data-nombre="<?php echo $value["nombre"] ?>" data-producto-tipo="<?php echo $prodTipo ?>" data-codigo-barra="<?php echo $value["codigoBarra"] ?>" data-id-stock="<?php echo $stockKey ?>" data-stock="<?php echo $prodStock ?>" data-id-producto="<?php echo $value["id"] ?>" data-precio-tipo="<?php echo $data["precioTipo"] ?>" data-precio="<?php echo $precio ?>">
                                                    <td style="line-height: 1em;"><span><?php echo $value["nombre"] ?></span><br><small>[<?php echo ((isset($prodTipo) && $prodTipo == "noCodificado") ? "PFC-".$value["compañia"]."-" : "").$value["codigoBarra"] ?>]</small></td>
                                                    <td class="text-right" style="white-space: nowrap !important;">
                                                        <div class="d-flex flex-column flex-nowrap">
                                                            <?php
                                                                if(is_numeric($prodStock) && $prodStock > 0){
                                                                    ?>
                                                                    <div style="line-height: .8em;" class="d-flex flex-column justify-content-center align-items-end mb-2 <?php echo $order[1]." ".(($data["precioTipo"] == 1) ? "h3 font-weight-bold" : "text-muted") ?>" id="titulo-precio-minorista"><span style="font-size: <?php echo (($data["precioTipo"] == 1) ? ".6em" : ".85em") ?>">Minorista</span> <span>$ <?php echo number_format($prodPrecio, 2, ",", "."); ?></span></div><br>
                                                                    <div style="line-height: .8em;" class="d-flex flex-column justify-content-center align-items-end mb-2 <?php echo $order[2]." ".(($data["precioTipo"] == 2) ? "h3 font-weight-bold" : "text-muted") ?>" id="titulo-precio-mayorista"><span style="font-size: <?php echo (($data["precioTipo"] == 2) ? ".6em" : ".85em") ?>">Mayorista</span> <span>$ <?php echo number_format($prodPrecioMayorista, 2, ",", "."); ?></span></div><br>
                                                                    <div style="line-height: .8em;" class="d-flex flex-column justify-content-center align-items-end mb-2 <?php echo $order[3]." ".(($data["precioTipo"] == 3) ? "h3 font-weight-bold" : "text-muted") ?>" id="titulo-precio-kiosco"><span style="font-size: <?php echo (($data["precioTipo"] == 3) ? ".6em" : ".85em") ?>">Kiosco</span> <span>$ <?php echo number_format($prodPrecioKiosco, 2, ",", "."); ?></span></div>
                                                                    <?php
                                                                }else{
                                                                    echo $prodStock;
                                                                } 
                                                            ?> 
                                                        </div> 
                                                    </td>
                                                    <td id="producto-accion-<?php echo $stockKey ?>">
                                                        <?php
                                                            if(is_numeric($prodStock) && $prodStock > 0){
                                                                ?>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="$('#cantidad-<?php echo $stockKey ?>').val(parseInt($('#cantidad-<?php echo $stockKey ?>').val()) - 1)"><i class="fa fa-minus"></i></button>
                                                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="$('#cantidad-<?php echo $stockKey ?>').val(parseInt($('#cantidad-<?php echo $stockKey ?>').val()) + 1)"><i class="fa fa-plus"></i></button>
                                                                    </div>
                                                                    <input type="number" class="form-control" value="1" min="1" max="<?php echo $prodStock ?>" id="cantidad-<?php echo $stockKey ?>" name="cantidad-<?php echo $stockKey ?>" disabled readonly> 
                                                                    <div class="input-group-append">
                                                                        <button type="button" onclick="test(<?php echo $stockKey ?>)" class="btn btn-sm btn-success"><i class="fa fa-cart-plus"></i></button>
                                                                    </div> 
                                                                </div>
                                                                <?php
                                                            }else{
                                                                echo $prodStock;
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        }else{
                                            ?>
                                            <tr>
                                                <td colspan="3" class="text-center"><u>No se encontraron resultados</u> con las palabras claves ingresadas. <b>Intente nuevamente.</b></td>
                                                <td class="d-none"></td>
                                                <td class="d-none"></td>
                                            </tr>
                                            <?php
                                        }
                                    ?>
                                </tbody>
                            </table>
                            <script> 
                                function test(idStock){
                                    let producto = document.getElementById("producto-stock-" + idStock);
                                    let cantidad = parseInt($("#cantidad-" + idStock).val());
                                    let data = [];
                                    if(cantidad > 0 && cantidad <= producto.dataset.stock){ 
                                        data.push({
                                            "nombre": producto.dataset.nombre,
                                            "idProducto": producto.dataset.idProducto,
                                            "idStock": producto.dataset.idStock,
                                            "stock": producto.dataset.stock,
                                            "codigoBarra": producto.dataset.codigoBarra,
                                            "precio": producto.dataset.precio,
                                            "precioTipo": producto.dataset.precioTipo,
                                            "productoTipo": producto.dataset.productoTipo,
                                            "cantidad": cantidad,
                                        });
                                        cart.push(data);
                                        
                                        var badge = $("#sucursal-pedido-container #btn-cart .badge"); 
                                        badge.html((parseInt(badge.html()) + 1));

                                        $("#producto-accion-" + idStock).html(loading("loader-ok"))

                                    }else{
                                        console.log(producto);
                                        console.log(cantidad + " - " + producto.dataset.stock);
                                        alert("La cantidad seleccionada es incorrecta.");
                                    }
                                }
                                $('#tabla-producto-filtrado').DataTable({
                                    "sDom": '<"d-flex justify-content-between"p>rt<"d-flex justify-content-between"il><"clear">',
                                    "lengthMenu": [ [4, 8, 25, 50, 100, -1], [4, 8, 25, 50, 100, "Todos"] ],
                                    "pageLength": 4,
                                    "bSort": false,
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
                            </script>
                            <?php
                        }else{
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al recibir la información de los productos. <b>Intente nuevamente o contacte al administrador.</b>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al recibir la información de la base de productos. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - sucursalPedidoFormularioProductoFiltrar - Usuario no logueado.');
            }
        }

        public static function sucursalPedidoCarritoFormulario($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    $total = 0;
                    $articulos = 0;
                    ?>
                    <div class="cover-container">
                        <div class="mine-container w-75 h-75">
                            <div class="d-flex justify-content-between"> 
                                <div class="titulo">Carrito de pedido - <?php echo '<span id="items-value">'.count($data["producto"]).'</span> items.' ?></div>
                                <button type="button" onclick="$('.cover-container').remove()" class="btn btn-danger"><i class="fa fa-times"></i></button>
                            </div>
                            <div class="w-100 d-block" style="height: 85%; overflow: auto">
                                <div id="carrito-pedido-process" style="display: none;"></div>
                                <form id="carrito-pedido-form" class="h-100" action="./engine/compania/sucursal-pedido-carrito-formulario-registro.php" form="#carrito-pedido-form" process="#carrito-pedido-process">
                                    <div class="d-flex" style="height: 100%;overflow: hidden;">
                                        <div style="width: 55%; height: 100%;">
                                            <div class="mine-container sm h-100" style="overflow-y: auto;">
                                                <h3 class="font-weight-bold">Productos</h3>
                                                <table id="pedido-lista-productos" class="table table-hover"> 
                                                    <tbody>
                                                        <?php
                                                            if(count($data["producto"]) > 0){
                                                                foreach($data["producto"] AS $key => $value){
                                                                    $total += (intval($value["cantidad"]) * floatval($value["precio"]));
                                                                    $articulos += $value["cantidad"];
                                                                    //echo '<script>console.log("('.number_format($value["precio"], 2, ".", ",").') '.$value["cantidad"].' X '.floatval($value["precio"]).' = '.$total.'")</script>';
                                                                    ?>
                                                                    <tr id="lista-producto-<?php echo $key ?>" data-stock="<?php echo $value["stock"] ?>" data-precio="<?php echo $value["precio"] ?>" data-nombre="<?php echo $value["nombre"] ?>" data-id="<?php echo $value["idProducto"] ?>">
                                                                        <td class="d-flex"> 
                                                                            <div class="d-flex align-items-center justify-content-center mr-2">
                                                                                <button type="button" onclick="setTimeout(() => { delItem(<?php echo $key ?>) }, 250)" class="btn btn-sm btn-danger" style="height: min-content;"><i class="fa fa-times"></i></button>
                                                                            </div>
                                                                            <div class="w-50" style="line-height: 1em;">
                                                                                <span class="font-weight-bold"><?php echo $value["nombre"] ?></span><br>
                                                                                <small class="text-muted">[<?php echo (($value["productoTipo"] == "noCodificado") ? "PFC-".$_SESSION["usuario"]->getCompañia()."-" : "").$value["codigoBarra"] ?>]</small>
                                                                                <div class="d-none">
                                                                                    <input type="text" class="form-control d-none" value="<?php echo $value["idProducto"] ?>" id="id-<?php echo $key ?>" name="producto-id[]" readonly>
                                                                                    <input type="text" class="form-control d-none" value="<?php echo $value["nombre"] ?>" id="nombre-<?php echo $key ?>" name="producto-descripcion[]" readonly>
                                                                                    <input type="text" class="form-control d-none" value="<?php echo $value["idStock"] ?>" id="idStock-<?php echo $key ?>" name="producto-identificador[]" readonly> //idstock para venta cc => pedido
                                                                                    <input type="text" class="form-control d-none" value="<?php echo $value["codigoBarra"] ?>" id="codigoBarra-<?php echo $key ?>" name="producto-codigo-barra[]" readonly>
                                                                                    <input type="text" class="form-control d-none" value="<?php echo $value["precio"] ?>" id="precio-<?php echo $key ?>" name="producto-precio-unitario[]" readonly>
                                                                                    <input type="text" class="form-control d-none" value="<?php echo $value["precioTipo"] ?>" id="precioTipo-<?php echo $key ?>" name="producto-precio-tipo[]" readonly>
                                                                                    <input type="text" class="form-control d-none" value="<?php echo $value["productoTipo"] ?>" id="productoTipo-<?php echo $key ?>" name="producto-tipo[]" readonly>
                                                                                </div>
                                                                            </div>
                                                                            <div class="w-50 d-flex align-items-top justify-content-end">
                                                                                <span class="font-weight-bold mr-3" style="font-size: 1em;">$ <?php echo number_format($value["precio"], 2, ",", ".") ?></span>
                                                                                <div id="lista-producto-<?php echo $key ?>-accion">
                                                                                    <div class="input-group">
                                                                                        <div class="input-group-prepend">
                                                                                            <button type="button" id="sub" class="btn btn-sm btn-outline-info" onclick="setTimeout(() => { subItem(<?php echo $key ?>) }, 0)"><i class="fa fa-minus"></i></button>
                                                                                        </div>
                                                                                        <input type="number" class="form-control" style="width: 3em" value="<?php echo $value["cantidad"] ?>" min="1" max="<?php echo $value["stock"] ?>" id="cantidad-<?php echo $key ?>" name="producto-cantidad[]" readonly=""> 
                                                                                        <div class="input-group-append">
                                                                                            <button type="button" id="add" class="btn btn-sm btn-outline-info" onclick="setTimeout(() => { addItem(<?php echo $key ?>) }, 0)"><i class="fa fa-plus"></i></button>
                                                                                        </div> 
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <?php
                                                                }
                                                            }else{
                                                                ?>
                                                                <tr>
                                                                    <td class="text-center w-100">
                                                                        <small class="text-muted">Sin productos en el carrito.</small>
                                                                    </td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        ?>
                                                    </tbody>
                                                </table> 
                                            </div> 
                                        </div>
                                        <div style="width: 45%">
                                            <div class="mine-container sm">
                                                <h3 class="font-weight-bold">Detalle</h3>
                                                <div class="p-1">
                                                    <div class="d-flex flex-column mb-3" style="line-height: 1.15em">
                                                        <small class="text-muted">Cliente</small>
                                                        <span style="font-size: 1.4em;"><?php echo mb_strtoupper($data["cliente"]["nombre"]) ?></span>
                                                        <div class="d-none">
                                                            <input type="text" class="form-control d-none" id="idCliente" name="cliente" value="<?php echo $data["cliente"]["id"] ?>" readonly>
                                                            <input type="text" class="form-control d-none" id="cliente" name="cliente-nombre" value="<?php echo $data["cliente"]["nombre"] ?>" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column mb-3" style="line-height: 1.15em">
                                                        <small class="text-muted">Artículos</small>
                                                        <span style="font-size: 1.4em;" id="articulos-value"><?php echo $articulos ?></span>
                                                        <div class="d-none">
                                                            <input type="text" class="form-control d-none" id="articulos" name="articulos" value="<?php echo $articulos ?>" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column mb-3" style="line-height: 1.15em">
                                                        <small class="text-muted">Total</small>
                                                        <div style="font-size: 1.7em;">$ <span id="total-value" data-total="<?php echo $total ?>"><?php echo number_format($total, 2, ".", "") ?></span></div>
                                                        <div class="d-none">
                                                            <input type="text" class="form-control d-none" id="descuento" name="descuento" value="0" readonly>
                                                            <input type="text" class="form-control d-none" id="iva" name="iva" value="1" readonly>
                                                            <input type="text" class="form-control d-none" id="pago" name="pago" value="8" readonly>
                                                            <input type="text" class="form-control d-none" id="pedido" name="pedido" value="1" readonly>
                                                            <input type="text" class="form-control d-none" id="idCaja" name="idCaja" value="0" readonly>
                                                            <input type="text" class="form-control d-none" id="total" name="monto-contado" value="<?php echo $total ?>" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column mb-3" style="line-height: 1.15em">
                                                        <div class="form-group">
                                                            <label for="observacion"><small class="text-muted">Observaciones</small></label>
                                                            <textarea class="form-control" id="observacion" name="observacion" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-center mt-5">
                                                        <button type="button" onclick="compañiaSucursalPedidoCarritoFormularioRegistrar()" class="btn btn-lg btn-success">Registrar pedido</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </form>
                            </div> 
                        </div>
                        <script>
                            function addItem(key){
                                var btnControl = $("#lista-producto-" + key + "-accion #add");
                                btnControl.prop("disabled", true);
                                var cantidad = parseInt($('#cantidad-' + key).val());
                                var producto = document.getElementById("lista-producto-" + key);
                                var total = document.getElementById("total-value");
                                var articulos = $("#articulos-value");
                                if(cantidad <= producto.dataset.stock){
                                    var nuevoTotal = (parseFloat(total.dataset.total) + parseFloat(producto.dataset.precio)).toFixed(2);
                                    $('#cantidad-' + key).val(cantidad + 1);
                                    $("#articulos").val(parseInt(articulos.html()) + 1);
                                    $("#total-value").html(nuevoTotal);
                                    total.dataset['total'] = nuevoTotal;
                                    $("#total").val(nuevoTotal);
                                    articulos.html(parseInt(articulos.html()) + 1);
                                }else{
                                    alert("No hay stock disponible para agregar.")  
                                } 
                                setTimeout(() => { btnControl.prop("disabled", false); }, 850);
                            }
                            function subItem(key){
                                var btnControl = $("#lista-producto-" + key + "-accion #sub");
                                btnControl.prop("disabled", true);
                                var cantidad = parseInt($('#cantidad-' + key).val());
                                var producto = document.getElementById("lista-producto-" + key);
                                var total = document.getElementById("total-value");
                                var articulos = $("#articulos-value");
                                if(cantidad > 1){
                                    var nuevoTotal = (parseFloat(total.dataset.total) - parseFloat(producto.dataset.precio)).toFixed(2);
                                    $('#cantidad-' + key).val(cantidad - 1);
                                    $("#articulos").val(parseInt(articulos.html()) - 1);
                                    $("#total-value").html(nuevoTotal);
                                    total.dataset['total'] = nuevoTotal;
                                    $("#total").val(nuevoTotal);
                                    articulos.html(parseInt(articulos.html()) - 1);
                                }else{
                                    alert("La cantidad mínima es 1 artículo.")  
                                } 
                                setTimeout(() => { btnControl.prop("disabled", false); }, 850);
                            }
                            var itemDeleted = 0; 
                            function delItem(key){ 
                                //cart = cart.filter((variable, i, item) => { i != (key - itemDeleted) });
                                var cantidad = parseInt($('#cantidad-' + key).val());
                                var producto = document.getElementById("lista-producto-" + key);
                                var total = document.getElementById("total-value");
                                var cartBadge = $("#sucursal-pedido-container #btn-cart .badge");
                                var articulos = $("#articulos-value");
                                var items = $("#items-value");
                                var restar = parseFloat(producto.dataset.precio) * cantidad;
                                cart = filtrarPorPropiedad(cart, "idProducto", producto.dataset.id, 0);
                                articulos.html(parseInt(articulos.html()) - cantidad);
                                $("#articulos").val(parseInt(articulos.html()) - cantidad);
                                $("#total-value").html((parseFloat(total.dataset.total) - parseFloat(restar)).toFixed(2));
                                $("#total").val((parseFloat(total.dataset.total) - parseFloat(restar)).toFixed(2));
                                total.dataset['total'] = (parseFloat(total.dataset.total) - parseFloat(restar)).toFixed(2);
                                producto.remove();
                                items.html(parseInt(items.html()) - 1);
                                cartBadge.html(parseInt(cartBadge.html()) - 1);
                                itemDeleted += 1;
                            }
                        </script>
                    </div>
                    <?php
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información del carrito de pedidos. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - sucursalPedidoCarritoFormulario - Usuario no logueado.');
            }
        }

        public static function sucursalPedidoFormulario($idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar(); 
                $sucursal = (is_numeric($idSucursal)) ? $idSucursal : $_SESSION["usuario"]->getSucursal();
                $compañia = (is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia(); 
                $dataCliente = $_SESSION["lista"]["compañia"][$compañia]["cliente"];
                ?>
                <div id="sucursal-pedido-container" class="mine-container">
                    <button type="button" onclick="compañiaSucursalPedidoFormulario()" class="btn btn-success d-none"><i class="fa fa-repeat"></i> Reload</button>
                    <div class="d-flex justify-content-between"> 
                        <div class="titulo">Nuevo pedido</div>
                        <button type="button" id="btn-cart" onclick="compañiaSucursalPedidoCarritoFormulario()" class="btn btn-outline-info" style="position: relative; padding: 1em; font-size: 1.2em;"><i class="fa fa-shopping-cart"></i><span class="badge badge-pill badge-success" style="right: 3px;">0</span></button>
                    </div>
                    <div class="p-1">
                        <div id="sucursal-pedido-process" style="display: none"></div>
                        <form id="sucursal-pedido-form" action="./" form="#sucursal-pedido-form" process="#sucursal-pedido-process">
                            <div class="row">
                                <div class="col-md-5">
                                    <div id="container-cliente" class="form-group">
                                        <label for="cliente" class="d-block"><i class="fa fa-search"></i> Buscar cliente</label>
                                        <select class="form-control" id="cliente" name="cliente">
                                            <option value=""> - Seleccionar cliente - </option>
                                            <?php
                                                if(is_array($dataCliente) && count($dataCliente) > 0){
                                                    foreach($dataCliente AS $key => $value){
                                                        echo '<option value="'.$value["id"].'" data-nombre="'.$value["nombre"].'">['.$value["documento"].'] '.$value["nombre"].', '.$value["domicilio"].'</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div id="step-2" style="display: none">
                                        <div class="d-flex justify-content-around">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="precioTipo" id="precioTipo1" value="1" checked="">
                                                    Precio Minorista
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="precioTipo" id="precioTipo2" value="2">
                                                    Precio Mayorista
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="radio" class="form-check-input" name="precioTipo" id="precioTipo3" value="3">
                                                    Precio Kiosco
                                                </label>
                                            </div>
                                        </div>
                                        <div id="container-tag" class="form-group">
                                            <label class="col-form-label" for="tag"><i class="fa fa-tag"></i> Palabras claves</label>
                                            <div class="input-group"> 
                                                <input type="text" class="form-control" placeholder="Ej.: shampoo, sedal, 200ml" id="tag" autocomplete="off">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </div>
                                            <small class="text-muted">Utilizá combinaciones <b>claves</b> como "rollo, cocina, x3" o "acond, elvive, 400ml".</small>
                                        </div>
                                        <div id="tag-badge"></div>
                                        <div id="tag-agregadas" class="form-group"></div>
                                    </div>
                                </div>
                                <div class="col-md-7" id="sucursal-pedido-producto-lista-process"></div>
                            </div>
                        </form> 
                    </div>
                    <script>
                        var tsCliente = tailSelectSet("#cliente");
                        $("#cliente").on("change", (e) => {
                            if($("#cliente").val() == ""){
                                $("#step-2").hide(150);
                                $("#step-2 #tag").val("");
                                $("#step-2 #tag-badge").html("");
                                $("#step-2 #tag-agregadas").html("");
                                $("#step-2 #sucursal-pedido-producto-lista-process").html("");
                            }else{
                                if("0" in cart){
                                    cart.shift();
                                    cart.unshift({name: "cliente", value: [{name: "id", value: $("#cliente").val()}, {name: "nombre", value: $("#cliente option:selected").text()} ]}); 
                                }else{
                                    cart.push({name: "cliente", value: [{name: "id", value: $("#cliente").val()}, {name: "nombre", value: $("#cliente option:selected").text()} ]});
                                }
                                $("#step-2").show(150);
                            }
                        });
                        $("#sucursal-pedido-form #container-tag input").on("keypress", (e) => {
                            let keycode = (e.keyCode ? e.keyCode : e.which);
                            if(keycode == '13'){
                                let input = $("#sucursal-pedido-form #container-tag input");
                                let tag = input.attr("id") + "-agregadas";
                                let tags = document.getElementById(tag);
                                if(input.val() == ""){
                                    if(tags.hasChildNodes()){
                                        compañiaSucursalPedidoFormularioProductoFiltrar(tag);
                                    }else{
                                        alert("Ingrese un valor a buscar.");
                                    } 
                                }else{
                                    agregarInput(input.attr("id"),input.val());
                                    input.val("");
                                    setTimeout(() => { compañiaSucursalPedidoFormularioProductoFiltrar(tag); }, 350);
                                }
                            }
                        });
                        $("#sucursal-pedido-form #container-tag button").on("click", (e) => {
                            let input = $("#sucursal-pedido-form #container-tag input");
                            let tag = input.attr("id") + "-agregadas";
                            let tags = document.getElementById(tag);
                            if(input.val() == ""){
                                if(tags.hasChildNodes()){
                                    compañiaSucursalPedidoFormularioProductoFiltrar(tag);
                                }else{
                                    alert("Ingrese un valor a buscar.");
                                } 
                            }else{
                                agregarInput(input.attr("id"),input.val());
                                input.val("");
                                setTimeout(() => { compañiaSucursalPedidoFormularioProductoFiltrar(tag); }, 350);
                            }
                        });
                    </script>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'compania.class.php - sucursalPedidoFormulario - Usuario no logueado.');
            }
        }

        public static function sucursalPedido($idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $sucursal = (is_numeric($idSucursal)) ? $idSucursal : $_SESSION["usuario"]->getSucursal();
                $compañia = (is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia();
                $data = Compania::sucursalPedidoGetData($idSucursal, $idCompañia);
                ?>
                <div class="mine-container">
                    <div class="d-flex justify-content-between"> 
                        <div class="titulo">Lista de pedidos</div>
                        <button type="button" onclick="compañiaSucursalPedidoFormulario()" class="btn btn-success"><i class="fa fa-plus"></i> Nuevo pedido</button>
                    </div>
                    <div class="p-1">
                        <?php
                            if(is_array($data)){
                                ?>
                                <table id="tabla-pedidos" class="table table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th scope="row">Cliente</th>
                                            <th></th>
                                            <th class="text-center">Pedido</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            if(count($data) > 0){
                                                foreach($data AS $key => $value){
                                                    $clienteNombre = Cliente::getNombre($value["cliente"]);
                                                    $clienteDireccion = Cliente::getDomicilio($value["cliente"]);
                                                    if($value["estado"] == 1){
                                                        if(!is_null($value["fechaPago"]) && $value["fechaPago"] != ""){
                                                            $estadoTexto = "Entregado / Cobrado";
                                                            $estadoTipo = "success";
                                                        }else{
                                                            $estadoTexto = "Pendiente";
                                                            $estadoTipo = "info";
                                                        }
                                                    }else{
                                                        $estadoTexto = "Anulado";
                                                        $estadoTipo = "danger";
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <?php
                                                                if($estadoTexto != "Anulado"){
                                                                    ?>
                                                                    <button type="button" onclick="ventaAnularFormulario(<?php echo $value['id'] ?>)" id="anular-<?php echo (($estadoTexto == "Pendiente") ? "pedido" : "venta") ?>" class="btn btn-sm btn-danger"><i class="fa fa-ban"></i></button>
                                                                    <?php
                                                                }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-column" style="line-height: 1em;">
                                                                <small class="text-muted">Nombre</small>
                                                                <span class="font-weight-bold"><?php echo mb_strtoupper((isset($clienteNombre) && !is_bool($clienteNombre) && strlen($clienteNombre) > 0) ? $clienteNombre : "S/N") ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-column" style="line-height: 1em;">
                                                                <small class="text-muted">Dirección</small>
                                                                <span class="font-weight-bold"><?php echo mb_strtoupper((isset($clienteDireccion) && !is_bool($clienteDireccion) && strlen($clienteDireccion) > 0) ? $clienteDireccion : "S/D") ?></span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" onclick="facturaVisualizar(<?php echo $value['id'] ?>)" id="factura" class="btn btn-sm btn-outline-info"><i class="fa fa-file-pdf-o"></i></button>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-pill badge-<?php echo $estadoTipo ?> p-2 w-75"><?php echo $estadoTexto ?></span>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                                if(Caja::corroboraAcceso()){
                                                                    $idCaja = $_SESSION["usuario"]->getActividadCaja();
                                                                    $actividad = 1;
                                                                    if($value["pago"] == 8 && $value["estado"] == 1 && is_numeric($idCaja) && $idCaja > 0 && Compania::cajaCorroboraExistencia($idCaja)){
                                                                        echo '<button type="button" id="pagar" onclick="cajaPagoFormulario('.$idCaja.', null, '.$value["id"].')" class="btn btn-sm btn-info"><i class="fa fa-usd"></i></button>';
                                                                    }
                                                                } 
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            }else{
                                                ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No se encontraron pedidos en esta sucursal.</td>
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
                                <script>
                                    dataTableSet("#tabla-pedidos");
                                    tippy('#anular-pedido', {
                                        content: 'Anular pedido',
                                        delay: [150,150],
                                        animation: 'fade'
                                    });
                                    tippy('#anular-venta', {
                                        content: 'Anular pedido / cobro',
                                        delay: [150,150],
                                        animation: 'fade'
                                    });
                                    tippy('#factura', {
                                        content: 'Visualizar ticket',
                                        delay: [150,150],
                                        animation: 'fade'
                                    });
                                    tippy('#pagar', {
                                        content: 'Registrar pago de pedido',
                                        delay: [150,150],
                                        animation: 'fade'
                                    });
                                </script>
                                <?php
                            }else{
                                $mensaje['tipo'] = 'danger';
                                $mensaje['cuerpo'] = 'Hubo un error al recibir la información de los pedidos de la sucursal. <b>Intente nuevamente o contacte al administrador.</b>';
                                Alert::mensaje($mensaje);
                            }
                        ?>
                    </div>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'compania.class.php - sucursalPedido - Usuario no logueado.');
            }
        }

        public static function configurar($idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $compañia = (is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia();
                if($_SESSION["usuario"]->isAdmin() || $_SESSION["usuario"]->getRol() == 1){
                    ?>
                    <div class="mine-container">
                        <nav class="navbar navbar-expand navbar-dark bg-red-1">
                            <a class="navbar-brand" href="#"><img src="./image/compañia/<?php echo $compañia ?>/logo.png" height="35" /> <?php echo $_SESSION["lista"]["compañia"][$compañia]["nombre"] ?></a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCompania" aria-controls="navbarCompania" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>

                            <div class="collapse navbar-collapse" id="navbarCompania">
                                <ul class="navbar-nav justify-content-end">
                                    <li class="nav-item active">
                                        <a class="nav-link" href="#/"><i class="fa fa-home"></i> Dashboard <span class="sr-only">(current)</span></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#/"><i class="fa fa-bar-chart"></i> Estadísticas</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#/" id="navbarCompaniaDD1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-cogs"></i> Administración</a>
                                        <div class="dropdown-menu" aria-labelledby="navbarCompaniaDD1">
                                            <a class="dropdown-item" href="#/" onclick="compañiaAdministracionUsuario('#administracion-cliente-process')"><i class="fa fa-users"></i> Usuarios</a>
                                            <a class="dropdown-item" href="#/" onclick="compañiaAdministracionSucursal('#administracion-cliente-process')"><i class="fa fa-building"></i> Sucursales</a>
                                            <a class="dropdown-item" href="#/" onclick="compañiaFacturacion('#administracion-cliente-process')"><i class="fa fa-file-pdf-o"></i> Facturación</a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                        <div id="administracion-cliente-process" class="p-2"></div>
                    </div>
                    <?php
                }else{
                    Sistema::debug('error', 'compania.class.php - configurar - Acceso denegado. Ref.: '.$_SESSION["usuario"]->getRol());
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'No tenés acceso a esta sección. Si consideras esto un error, <b>contacta al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - configurar - Usuario no logueado.');
            }
        }

        public static function productoData($idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                return $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"];
            }else{
                Sistema::debug('error', 'compania.class.php - productoData - Usuario no logueado.');
            }
        }
        public static function estadisticaData($idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $producto = Compania::productoData();
                $productoMN = array_filter($producto, function($data){
                    return (is_numeric($data["producto"]));
                });
                $productoNC = array_filter($producto, function($data){
                    return (is_numeric($data["productoNC"]));
                });
            }else{
                Sistema::debug('error', 'compania.class.php - estadistica - Usuario no logueado.');
            }
        }

        public static function sucursalCajaData($idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("compañia_sucursal_caja", "*", "sucursal = '".((is_numeric($idSucursal)) ? $idSucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia())."'", "");
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
                    Sistema::debug('error', 'compania.class.php - sucursalCajaData - Error al consultar la información de las cajas. Ref.: '.DataBase::getError());
                }
                return false;
            }else{
                Sistema::debug('error', 'compania.class.php - sucursalCajaData - Usuario no logueado.');
            }
        }

        public static function cajaCorroboraExistencia($idCaja, $idSucursal = null, $idCompañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idCaja) && is_numeric($idCaja) && $idCaja > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_caja", "id", "id = '".$idCaja."' AND sucursal = '".((is_numeric($idSucursal)) ? $idSucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia())."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            return true;
                        }else{
                            Sistema::debug('error', 'compania.class.php - cajaCorroboraExistencia - Caja no encontrada. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'compania.class.php - cajaCorroboraExistencia - Error al consultar la información de la caja. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - cajaCorroboraExistencia - Error en identificador de caja. Ref.: '.$idCaja);
                }
            }else{
                Sistema::debug('error', 'compania.class.php - cajaCorroboraExistencia - Usuario no logueado.');
            }
            return false;
        }

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
                        $credito = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["credito"];
                        $prenComprobante = "";
                        for($i = 12; $i >= strlen($data[$idVenta]["nComprobante"]); $i--){
                            $prenComprobante .= "0";
                        }
                        if(count($data) > 0){
                            $producto = explode(",", $data[$idVenta]["producto"]);
                            $productoCantidad = explode(",", $data[$idVenta]["productoCantidad"]);
                            $productoPrecio = explode(",", $data[$idVenta]["productoPrecio"]);
                            ?>
                            <div id="container-factura" class="cover-container">
                                <div class="mine-container w-75 h-75">
                                    <div class="d-flex justify-content-between"> 
                                        <div class="titulo">Visualización comprobante #<?php echo $prenComprobante.$data[$idVenta]["nComprobante"] ?></div>
                                        <button type="button" onclick="$('.cover-container').remove()" class="btn btn-danger"><i class="fa fa-times"></i></button>
                                    </div>
                                    <div class="w-100" style="height: 85%; overflow: auto">
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
                                                    <span><b>Forma de Pago: </b><?php echo $_SESSION["lista"]["pago"][$data[$idVenta]["pago"]]["pago"] ?></span>
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
                                                                    <td style="padding: 0.215em 0; line-height: 0.8em;"><?php echo ($value == 0) ? "VARIOS" : $_SESSION["lista"]["producto"][$tipo][$dataCompañiaStock[$value][($tipo == "codificado") ? "producto" : "productoNC"]]["nombre"] ?></td>
                                                                    <td style="padding: 0.215em 0; text-align: center;"><?php echo $productoCantidad[$key] ?></td>
                                                                    <td style="padding: 0.215em 0; text-align: center;">$<span><?php echo $productoPrecio[$key] ?></span></td>
                                                                    <td style="padding: 0.215em 0; text-align: right;">$<span><?php echo round($productoCantidad[$key] * $productoPrecio[$key], 2) ?></span></td>
                                                                </tr>
                                                                <?php
                                                                $total += $productoCantidad[$key] * $productoPrecio[$key];
                                                            }
                                                        ?>
                                                    </tbody>
                                                    <tfoot style="border-top: 1px solid lightgray; border-bottom: 1px solid lightgray; ">
                                                        <tr style="margin-bottom: 1em; <?php echo (($data[$idVenta]["pago"] == 1 && $data[$idVenta]["descuento"] == 0) || $data[$idVenta]["pago"] == 2 || $data[$idVenta]["pago"] == 4) ? "display: none" : "" ?>">
                                                            <td style="padding: 0.215em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Total:</td>
                                                            <td style="padding: 0.215em 0; text-align: right">$ <?php echo round($data[$idVenta]["subtotal"], 2); ?></td>
                                                        </tr>
                                                        <tr class="margin-bottom: 1em; <?php echo ($data[$idVenta]["descuento"] == 0) ? "d-none" : "" ?>">
                                                            <td style="padding: 0.215em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Descuento:</td>
                                                            <td style="padding: 0.215em 0; text-align: right">$ <?php echo round(($data[$idVenta]["subtotal"] * ($data[$idVenta]["descuento"] / 100)), 2) ?></td>
                                                        </tr>
                                                        <?php
                                                            if($data[$idVenta]["pago"] == 4 || $data[$idVenta]["pago"] == 5){
                                                                ?> 
                                                                <tr>
                                                                    <td style="padding: 0.215em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Contado:</td>
                                                                    <td style="padding: 0.215em 0; text-align: right">$ <?php echo round($data[$idVenta]["contado"], 2); ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                            if($data[$idVenta]["pago"] == 2 || $data[$idVenta]["pago"] == 4 || $data[$idVenta]["pago"] == 6){
                                                                ?> 
                                                                <tr>
                                                                    <td style="padding: 0.215em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Débito:</td>
                                                                    <td style="padding: 0.215em 0; text-align: right">$ <?php echo round($data[$idVenta]["debito"], 2); ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                            if($data[$idVenta]["pago"] == 3 || $data[$idVenta]["pago"] == 5 || $data[$idVenta]["pago"] == 6){
                                                                ?> 
                                                                <tr>
                                                                    <td style="padding: 0.215em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Crédito:</td>
                                                                    <td style="padding: 0.215em 0; text-align: right; line-height: 0.8em;">$ <?php echo round(($data[$idVenta]["credito"] * $credito[$data[$idVenta]["financiacion"]]["interes"]), 2)."<br>".$credito[$data[$idVenta]["financiacion"]]["cuotas"]." cuotas" ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                            if($data[$idVenta]["iva"] == 1){
                                                                ?> 
                                                                <tr style="display: none">
                                                                    <td style="padding: 0.215em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Iva :</td>
                                                                    <td style="padding: 0.215em 0; text-align: right">% 21</td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        ?>
                                                        <tr style="margin-top: 1em; ">
                                                            <td style="padding: 0.215em 0; text-align: right; font-weight: bold; font-size: 1.15em;" colspan="3">Total a pagar:</td>
                                                            <td style="padding: 0.215em 0; text-align: right">$ <?php echo round($data[$idVenta]["total"] - ($data[$idVenta]["subtotal"] / 100 * (($data[$idVenta]["iva"] == 1) ? 0 : 21)), 2) ?></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                                <?php
                                                    if(strlen($data[$idVenta]["observacion"]) > 0){
                                                        ?>
                                                        <div style="margin-top: 1em;">
                                                            <div style="border-top: 1px dashed darkgray; padding: 0.75em;">
                                                                <b>Observaciones para el operador:</b>
                                                                <div style="padding-top: 0.65em">
                                                                    <?php echo $data[$idVenta]["observacion"] ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                    </div> 
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
                            $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"][DataBase::getLastId()] = [
                                "id" => DataBase::getLastId(),
                                "producto" => (($codificado) ? $data["idProducto"] : ""),
                                "productoNC" => ((!$codificado) ? $data["idProducto"] : ""),
                                "sucursal" => $_SESSION["usuario"]->getSucursal(),
                                "compañia" => $_SESSION["usuario"]->getCompañia(),
                                "stock" => ((is_numeric($data["stock"])) ? $data["stock"] : 0),
                                "minimo" => ((is_numeric($data["minimo"])) ? $data["minimo"] : 0),
                                "maximo" => ((is_numeric($data["maximo"])) ? $data["maximo"] : 0),
                                "precio" => ((is_numeric($data["precio"])) ? $data["precio"] : 0),
                                "precioMayorista" => ((is_numeric($data["precioMayorista"])) ? $data["precioMayorista"] : 0),
                                "precioKiosco" => ((is_numeric($data["precioKiosco"])) ? $data["precioKiosco"] : 0),
                                "operador" => $_SESSION["usuario"]->getId(),
                                "fechaModificacion" => "",
                                "fechaCarga" => Date::current()
                            ];
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

        public static function productoNoCodifData($idCompañia = null, $idProducto = null, $data = null, $max = 250){
            Session::iniciar();
            $idCompañia = (is_null($idCompañia)) ? $_SESSION["usuario"]->getCompañia() : $idCompañia;
            if(is_array($data) && count($data) > 0){
                if($data["filtroOpcion"] == 1 || $data["filtroOpcion"] == 2){
                    switch($data["filtroOpcion"]){
                        case 1:
                            $cond = "";
                            if(is_array($data["tag"]) && count($data["tag"]) > 0){
                                foreach($data["tag"] AS $key => $value){
                                    $value = preg_replace( '/[\W]/', '', $value);
                                    if($key > 0 && $key < count($data["tag"])){
                                        $cond .= " AND ";
                                    }
                                    $cond .= "nombre LIKE '%".$value."%'";
                                }
                            }
                            $query = DataBase::select("compañia_producto", "*", $cond, "ORDER BY codigoBarra ASC, nombre ASC LIMIT ".$max);
                        break;
                        case 2:
                            $query = DataBase::select("compañia_producto", "*", "codigoBarra LIKE '".$data["codigo"]."%'", "ORDER BY codigoBarra ASC, nombre ASC LIMIT ".$max);
                        break;
                    }
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
                        Sistema::debug('error', 'compania.class.php - productoNoCodifData - Error al buscar los productos. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - productoNoCodifData - Error en el dato de búsqueda recibido. Ref.: '.$data["busqueda"]);
                }
            }else{ 
                $query = DataBase::select("compañia_producto", "*", "compañia = '".$idCompañia."'".((is_numeric($idProducto)) ? " AND id = '".$idProducto."'" : ""), "ORDER BY nombre ASC");
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
                    Sistema::debug('error', 'compania.class.php - productoNoCodifData - Error al consultar la inforamción de productos. Ref.: '.DataBase::getError());
                }
            }
            return false;
        }

        public static function stockFormulario(){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $data = Compania::stockData();
                Alert::feature(2);
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
                                    return;
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

                    <div id="stock-producto"> 
                        
                    </div>


                    <div class="p-1 w-100 mh-50 overflow-auto d-none"> <!-- Tabla vieja, cancelada -->
                        <table id="tabla-producto-inventario" class="table table-hover d-none">
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
                                    if(is_array($data) && false){
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
                                                    <th scope="row"><?php echo (($tipo == "noCodificado") ? "PFC-".$_SESSION["usuario"]->getCompañia()."-" : "").$producto[$tipo][$idProducto]["codigoBarra"] ?></th>
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
                                    productoEditarContenidoFormulario(dataKey,id,dataValue,productoTipo);
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
                                    productoEditarContenidoFormulario(dataKey,id,dataValue,productoTipo);
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
                            $("#buscador-input").on("keydown", (e) => {
                                let keycode = (e.keyCode ? e.keyCode : e.which);
                                let barcode = $("#buscador-input").val();
                                let strokes = [96,97,98,99,100,101,102,103,104,105];
                                switch(keycode){
                                    case 45:
                                        $("#buscador-input").val("PFC-<?php echo $_SESSION["usuario"]->getCompañia() ?>-").focus();    
                                    break;
                                    case 13:
                                        if(barcode.length > 0){
                                            stockProductoAgregarInput("stock-producto", barcode);
                                        }else{
                                            ventanaAlertaFlotante("Advertencia", "Debés ingresar algún valor para buscar...", $("#producto").focus());
                                        }
                                    break;
                                    case 17:
                                        e.preventDefault();
                                        break;
                                    case 46: 
                                        e.preventDefault();
                                        $("#buscador-input").val("").focus();
                                    break;
                                } 
                            });
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

        public static function stockData($idCompañia = null, $idSucursal = null, $fechaUpdate = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $fechaUpdateQuery = (!is_null($fechaUpdate)) ? " AND fechaModificacion IS NOT NULL AND fechaModificacion >= '".$fechaUpdate."'" : "";
                $query = DataBase::select("producto_stock", "*", "compañia = '".((is_numeric($idCompañia)) ? $idCompañia : $_SESSION["usuario"]->getCompañia())."' ".((is_numeric($idSucursal) && $idSucursal > 0) ? " AND sucursal = '".$idSucursal."'" : "" ).$fechaUpdateQuery, "");
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

        public static function productoLista(){
            $producto = Producto::FEChunkLoad(0, true);
            if(is_array($producto)){ 
                Session::iniciar();
                $_SESSION["usuario"]->setLastReloadBaseProducto();
                if($_SESSION["usuario"]->isAdmin()){
                    
                }
                ?>
                <ul id="companiaProductoLista" class="list-group d-none">
                    <?php
                        foreach(array_merge($producto["producto"]["noCodificado"]["lista"], $producto["producto"]["codificado"]["lista"]) AS $key => $value){
                            ?>
                            <li class="list-group-item producto" id="<?php echo (($value["data"]["tipo"] == 5) ? "PFC-".$_SESSION["usuario"]->getCompañia()."-" : "").$value["data"]["codigoBarra"] ?>" 
                            data-producto-id="<?php echo $value["data"]["id"] ?>" 
                            data-producto-nombre="<?php echo $value["data"]["nombre"] ?>" 
                            data-producto-codigoBarra="<?php echo (($value["data"]["tipo"] == 5) ? "PFC-".$_SESSION["usuario"]->getCompañia()."-" : "").$value["data"]["codigoBarra"] ?>"
                            data-producto-fechaUpdate="<?php echo $value["data"]["fechaUpdate"] ?>"
                            data-stock-id="<?php echo (is_array($value["stock"]) && count($value["stock"]) > 0) ? $value["stock"]["id"] : "0" ?>"
                            data-stock-productoId="<?php echo (is_array($value["stock"]) && count($value["stock"]) > 0) ? $value["stock"]["producto"] : "0" ?>"
                            data-stock-productoNCId="<?php echo (is_array($value["stock"]) && count($value["stock"]) > 0) ? $value["stock"]["productoNC"] : "0" ?>"
                            data-stock-stock="<?php echo (is_array($value["stock"]) && count($value["stock"]) > 0) ? $value["stock"]["stock"] : "0" ?>"
                            data-stock-precio="<?php echo (is_array($value["stock"]) && count($value["stock"]) > 0) ? $value["stock"]["precio"] : "0" ?>"
                            data-stock-precioMayorista="<?php echo (is_array($value["stock"]) && count($value["stock"]) > 0) ? $value["stock"]["precioMayorista"] : "0" ?>"
                            data-stock-precioKiosco="<?php echo (is_array($value["stock"]) && count($value["stock"]) > 0) ? $value["stock"]["precioKiosco"] : "0" ?>"
                            data-stock-operador="<?php echo (is_array($value["stock"]) && count($value["stock"]) > 0) ? $value["stock"]["operador"] : "0" ?>"
                            data-stock-fechaModificacion="<?php echo (is_array($value["stock"]) && count($value["stock"]) > 0) ? $value["stock"]["fechaModificacion"] : "0" ?>"
                            </li>
                            <?php
                        }
                    ?>
                </ul>
                <?php
            }
        }
    }
?>