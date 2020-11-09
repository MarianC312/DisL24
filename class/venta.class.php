<?php
    class Venta {
        public static function registrar($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
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
                            $dataProducto[$key]["id"] = $_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["producto"];
                            $dataProducto[$key]["idStock"] = $value;
                            $dataProducto[$key]["stock"] = $_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["stock"];
                            $dataProducto[$key]["precio"] = $_SESSION["lista"]["compañia"]["sucursal"]["stock"][$value]["precio"];
                            $dataProducto[$key]["cantidad"] = $data["producto-cantidad"][$key];
                            $dataProducto[$key]["nombre"] = $_SESSION["lista"]["producto"][$dataProducto[$key]["id"]]["nombre"];
                            $dataProducto[$key]["subtotal"] = $dataProducto[$key]["cantidad"] * $dataProducto[$key]["precio"];
                            $dataCaja["subtotal"] += $dataProducto[$key]["subtotal"];
                            if(strlen($dataCaja["producto"]) > 0){
                                $dataCaja["producto"] .= ",";
                            }
                            $dataCaja["producto"] .= $value;
                            if(strlen($dataCaja["productoCantidad"]) > 0){
                                $dataCaja["productoCantidad"] .= ",";
                            }
                            if(strlen($dataCaja["productoPrecio"]) > 0){
                                $dataCaja["productoPrecio"] .= ",";
                            }
                            $dataCaja["productoPrecio"] .= $dataProducto[$key]["precio"];
                            $dataCaja["productoCantidad"] .= $data["producto-cantidad"][$key];
                        }

                        foreach($dataProducto AS $key => $value){
                            if($value["stock"] < $value["cantidad"]){
                                $mensaje['tipo'] = 'warning';
                                $mensaje['cuerpo'] = 'El producto '.$value["nombre"].' no tiene stock disponible para venta. Stock disponible: '.$value["stock"].' - Cantidad solicitada: '.$value["cantidad"];
                                $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                                Alert::mensaje($mensaje);
                                exit;
                            }
                        }

                        if(!$dataCaja["iva"]){
                            $dataCaja["subtotal"] = $dataCaja["subtotal"] - ($dataCaja["subtotal"] / 100 * 21);
                        }

                        $dataCaja["total"] = $dataCaja["subtotal"] - ($dataCaja["subtotal"] / 100 * $dataCaja["descuento"]); 

                        $nComprobante = Compania::facturaIdUltima();
                        if(is_numeric($nComprobante) && $nComprobante >= 0){
                            $query = DataBase::insert("compañia_sucursal_venta", "nComprobante,producto,productoCantidad,productoPrecio,pago,descuento,iva,cliente,subtotal,total,operador,sucursal,compañia", "'".($nComprobante + 1)."','".$dataCaja["producto"]."','".$dataCaja["productoCantidad"]."','".$dataCaja["productoPrecio"]."','".$dataCaja["pago"]."','".$dataCaja["descuento"]."','".(($dataCaja["iva"]) ? 1 : 0)."',".((isset($dataCaja["cliente"]) && is_numeric($dataCaja["cliente"]) && $dataCaja["cliente"] > 0) ? $dataCaja["cliente"] : "NULL").",'".$dataCaja["subtotal"]."','".$dataCaja["total"]."','".$_SESSION["usuario"]->getId()."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."'");
                            if($query){
                                $idVenta = DataBase::getLastId();
                                $stockRestar = Compania::stockRestar($dataCaja["producto"], $dataCaja["productoCantidad"]);
                                if($stockRestar){
                                    $query = DataBase::update("compañia_sucursal_venta", "procesadoStock = 1", "id = '".$idVenta."' AND sucursal = '".$_SESSION["usuario"]->getSucursal()."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                                    if(!$query){
                                        Sistema::debug('error', 'venta.class.php - registrar - Error al procesar stock. Ref.: '.$idVenta);
                                    }
                                }else{
                                    Sistema::debug('error', 'venta.class.php - registar - Error al registrar movimiento en stock.');
                                }
                                if($dataCaja["pago"] == 1){
                                    $cajaInsertData = [
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
                                echo '<script>cajaUpdateMonto('.Caja::dataGetMonto().')</script>';
                                echo '<script>cajaHistorial();</script>';
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
                $dataCliente = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["cliente"];
                $baseProductos = $_SESSION["lista"]["producto"];
                $dataStock = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"];
                ?>
                <div id="container-venta-formulario" class="mine-container">
                    <div class="d-flex justify-content-between">
                        <div class="titulo">Registrar nueva venta</div>
                        <button type="button" onclick="$('#container-venta-formulario').remove();" class="btn delete"><i class="fa fa-times"></i></button>
                    </div>
                    <script> 
                         $(document).ready(function()
                            {
                                $("#producto").on("keyup", 
                                    function(){
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
                        <div class="">
                            <fieldset class="form-group d-flex justify-content-around">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" onchange="ventaRegistrarFormularioUpdateBusqueda()" name="tipoCliente" id="tipoCliente1" value="1" checked="">
                                        Comprador ocasional
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" onchange="ventaRegistrarFormularioUpdateBusqueda()" name="tipoCliente" id="tipoCliente2" value="2">
                                        Cliente
                                    </label>
                                </div>
                            </fieldset>
                        </div>
                        <div id="container-cliente" class="form-group">
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
                        <div id="container-producto" class="form-group">
                            <label for="producto" class="d-block"><i class="fa fa-plus"></i> Agregar producto</label>
                            <input type="text" class="form-control" placeholder="Buscar producto" id="producto" autocomplete="off">
                            <ul id="container-producto-lista" class="list-group" style="max-height: 15vh; overflow: auto;">
                                <?php
                                    if(is_array($dataStock) && count($dataStock) > 0){
                                        foreach($dataStock AS $key => $value){
                                            ?>
                                            <li class="list-group-item" id="c-p-c-b-<?php echo $baseProductos[$value["producto"]]["codigoBarra"] ?>" style="display: none"  data-id-producto="<?php echo $value["id"] ?>" data-producto="<?php echo $baseProductos[$value["producto"]]["nombre"] ?>" data-stock="<?php echo $value["stock"] ?>" data-precio="<?php echo $value["precio"] ?>" data-bar-code="<?php echo $baseProductos[$value["producto"]]["codigoBarra"] ?>">
                                                <div class="d-flex justify-content-between align-items-center" data-id-producto="<?php echo $value["id"] ?>" data-producto="<?php echo $baseProductos[$value["producto"]]["nombre"] ?>" data-stock="<?php echo $value["stock"] ?>" data-precio="<?php echo $value["precio"] ?>" data-bar-code="<?php echo $baseProductos[$value["producto"]]["codigoBarra"] ?>">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <?php echo $baseProductos[$value["producto"]]["codigoBarra"] ?>
                                                            <?php echo $baseProductos[$value["producto"]]["nombre"] ?>
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
                        <table id="tabla-venta-productos" data-sticky-header="true" class="table table-hover table-responsive w-100 tableFixHead"> 
                            <thead class="sticky-header">
                                <tr>
                                    <td class="fit" scope="row"></td>
                                    <td class="fit" scope="row"><i class="fa fa-barcode"></i> Código</td>
                                    <td class="w-100 fit">Descripción</td>
                                    <td class="fit">Precio x U.</td>
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
                            <button type="button" onclick="ventaRegistrar()" class="btn btn-success">Registrar venta</button>
                        </div>
                    </form>
                </div>
                <script>
                    $(document).ready(() => {
                        $("#producto").focus();
                    })

                    $("#producto").on("keypress", (e) => {
                        let keycode = (e.keyCode ? e.keyCode : e.which);
                        let barcode = $("#producto").val();
                        console.log(keycode);
                        switch(keycode){
                            case 13:
                                if(barcode.length > 0){
                                    let lista = [...document.getElementById("container-producto-lista").childNodes];
                                    lista.map((data, i) => {
                                        if(typeof data === 'object' && data.length > 0){
                                            //console.log(i + " " + data.dataset);
                                        }else{
                                            if(data.dataset.barCode === barcode){
                                                $("#" + data.id + " button").click();
                                            }
                                        }
                                    });
                                }else{
                                    ventaRegistrar();
                                }
                            break;
                            case 45: 
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

                    $("#iva").on("change", (e) => {
                        if($("#iva").is(":checked")){
                            $("#iva-valor").val("21");
                        }else{
                            $("#iva-valor").val("0");
                        }
                        cajaCalculaTotal();
                    })

                    tailSelectSet("#cliente");
                    ventaRegistrarFormularioUpdateBusqueda();
                </script>
                <?php
            }else{
                Sistema::debug('error', 'venta.class.php - registrarFormulario - Usuario no logueado.');
            }   
        }
    }
?>