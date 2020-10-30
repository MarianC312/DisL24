<?php
    class Compania{ 
        public static function reloadStaticData(){ 
            if(Sistema::usuarioLogueado()){
                $_SESSION["lista"]["compañia"]["cliente"] = Lista::compañiaCliente();
                $_SESSION["lista"]["compañia"]["sucursal"]["stock"] = Compania::stockData();
            }else{
                Sistema::debug('error', 'compania.class.php - reloadStaticData - Usuario no logueado.');
            }
        }

        public static function facturaData($idVenta, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idVenta) && is_numeric($idVenta) && $idVenta > 0){ 
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
                if(isset($idVenta) && is_numeric($idVenta) && $idVenta > 0){ 
                    $data = Compania::facturaData($idVenta, $sucursal, $compañia);
                    if(is_array($data)){
                        Session::iniciar();
                        $dataCompañia = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["data"][$_SESSION["usuario"]->getCompañia()];
                        $dataCompañiaStock = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"];
                        $nComprobante = "";
                        for($i = 12; $i >= strlen($idVenta); $i--){
                            $nComprobante .= "0";
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
                                    <img src="image/compañia/<?php echo $dataCompañia["id"] ?>/logo.png" height="150px" alt="<?php echo $dataCompañia["nombre"] ?>" />
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
                                        <span><b>Comprobante N°:</b> #<?php echo $nComprobante.$idVenta ?></span>
                                    </div>
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
                                                    ?>
                                                    <tr style="border-bottom: 1px solid lightgray;">
                                                        <td style="padding: 1.015em 0; "><?php echo $_SESSION["lista"]["producto"][$dataCompañiaStock[$value]["producto"]]["nombre"] ?></td>
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
                    $dataStock = $_SESSION["lista"]["compañia"]["sucursal"]["stock"];
                    $producto = explode(",", $producto); 
                    if(isset($productoCantidad) && !is_null($productoCantidad) && strlen($productoCantidad) > 0){
                        $productoCantidad = explode(",", $productoCantidad);
                        foreach($producto AS $key => $value){
                            if($dataStock[$value]["stock"] >= $productoCantidad[$key]){
                                $query = DataBase::update("producto_stock", "stock = stock - '".$productoCantidad[$key]."', operador = '".$_SESSION["usuario"]->getId()."'", "id = '".$value."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'"); 
                                $response[$key]["id"] = $value;
                                $response[$key]["cantidad"] = $productoCantidad[$key];
                                if($query){
                                    $response[$key]["status"] = true; 
                                }else{
                                    $response[$key]["status"] = false;
                                }
                            }else{
                                Sistema::debug('error', 'compania.class.php - stockRestar - El producto '.$dataStock[$value]["nombre"].' ['.$value.'] no tiene stock disponible. Stock disponible: '.$dataStock[$key]["stock"].' - Cantidad solicitada: '.$productoCantidad[$key]);
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
                    <div class="p-1">
                        <table id="tabla-producto-inventario" class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Producto</th>
                                    <th class="text-center" scope="col">Stock</th>
                                    <th class="text-center" scope="col">Mínimo</th>
                                    <th class="text-center" scope="col">Máximo</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Categoría</th>
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
                                                }else{
                                                    $prodStock = null;
                                                    $prodMin = null;
                                                    $prodMax = null;
                                                }
                                                ?>
                                                <tr id="producto-<?php echo $value["id"] ?>" data-key="<?php echo $value["id"] ?>">
                                                    <th scope="row"><?php echo $value["codigoBarra"] ?></th>
                                                    <td style="display: flex; flex-direction: column-reverse;"><?php echo (($enStock) ? '<span class="badge badge-success" style="width: fit-content;"><i class="fa fa-check-square-o"></i> En stock</span> ' : '').$value["nombre"] ?></td>
                                                    <td id="stock" data-value="<?php echo (isset($prodStock) && is_numeric($prodStock)) ? $prodStock : 0 ?>" class="text-center <?php echo (!$enStock) ? "opacity-0" : "" ?>"><?php echo (isset($prodStock)) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$prodStock.'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i> stock inicial</a>" ?></td>
                                                    <td id="minimo" data-value="<?php echo (isset($prodMin) && is_numeric($prodMin)) ? $prodMin : 0 ?>" class="text-center <?php echo (!$enStock) ? "opacity-0" : "" ?>"><?php echo (isset($prodMin) && is_numeric($prodMin)) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$prodMin.'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="maximo" data-value="<?php echo (isset($prodMax) && is_numeric($prodMax)) ? $prodMax : 0 ?>" class="text-center <?php echo (!$enStock) ? "opacity-0" : "" ?>"><?php echo (isset($prodMax) && is_numeric($prodMax)) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$prodMax.'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
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
                                                        <td colspan="9" class="text-center">
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
                                                    </tr>
                                                    <?php
                                                    break;
                                                }
                                            } 
                                        }else{
                                            ?> 
                                            <tr>
                                                <td colspan="9" class="text-center">
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
                                                            buscar una serie de productos de un importador o productor <b>te recomendamos</b> ingresar hasta <u>8 números del código</u> para que la búsqueda sea satisfactoria. <br><br><b>Si estás utilizando el código completo y el producto no aparece, contactá a administración para realizar el registro.</b>';
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
                                            <td colspan="9" class="text-center">
                                                Hubo un error al encontrar los productos de la compañía. <b>Intente nuevamente o contacte al administrador.</b>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <script> 
                        $(document).ready(function() {
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

        public static function stockRegistroProductoFormulario(){
            if(Sistema::usuarioLogueado()){
                ?>
                <div class="mine-container">
                    <div class="titulo">Registrar un producto de la base de productos en mi stock:</div>
                    <form id="compania-stock-registro-producto-form" onsubmit="return false;" action="./includes/compania/stock-registro-producto-lista-formulario.php" form="#compania-stock-registro-producto-form" process="#compania-stock-registro-producto-process">
                        <fieldset class="form-group">
                            <div class="d-flex justify-content-around">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" onchange="compañiaRegistroProductoUpdateBusqueda()" name="filtroOpcion" id="filtroOpcion1" value="1" checked="">
                                        Filtrar por Etiquetas <i class="fa fa-tag"></i>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" onchange="compañiaRegistroProductoUpdateBusqueda()" name="filtroOpcion" id="filtroOpcion2" value="2">
                                        Filtrar por Código de barra <i class="fa fa-barcode"></i>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                        <div id="container-codigo" class="form-group" style="display: none">
                            <label class="col-form-label" for="codigo"><i class="fa fa-barcode"></i> Código de barra</label>
                            <input type="text" class="form-control" placeholder="Ingresá el código del producto" id="codigo" name="codigo">
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
                    compañiaRegistroProductoUpdateBusqueda();
                </script>
                <?php
            }else{
                Sistema::debug('error', 'compania.class.php - stockRegistroProductoFormulario - Usuario no logueado.');
            }
        }

        public static function stockGetData($idProducto, $tipo = null, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    Session::iniciar();
                    $query = DataBase::select("producto_stock", ((!is_null($tipo)) ? $tipo : "*"), "producto = '".$idProducto."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
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

        public static function stockContenido($idProducto, $tipo){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0 && isset($tipo) && strlen($tipo) > 0){
                    $data = Compania::stockGetData($idProducto, $tipo);
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

        public static function stockGetId($idProducto, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    $query = DataBase::select("producto_stock", "id", "producto = '".$idProducto."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
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

        public static function stockCorroboraExistencia($idProducto, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    Session::iniciar();
                    $query = DataBase::select("producto_stock", "id", "producto = '".$idProducto."' AND sucursal = '".((is_numeric($sucursal)) ? $sucursal : $_SESSION["usuario"]->getSucursal())."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "");
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
                    if(is_numeric($codigoProducto) && !is_bool($codigoProducto)){
                        if(Producto::corroboraExistencia(["codigo" => $codigoProducto])){
                            $productoEnStock = Compania::stockCorroboraExistencia($data["idProducto"]);
                            Session::iniciar();
                            if(is_bool($productoEnStock) && $productoEnStock){
                                $idProductoStock = Compania::stockGetId($data["idProducto"]);
                                if(is_numeric($idProductoStock) && $idProductoStock > 0){
                                    $query = DataBase::update("producto_stock", $data["tipo"]." = ".$data["cantidad"], "id = '".$idProductoStock."' AND producto = '".$data["idProducto"]."' AND sucursal = '".$_SESSION["usuario"]->getSucursal()."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                                    if($query){
                                        echo '<script>successAction("#producto-'.$data["idProducto"].' #'.$data["tipo"].'", () => { return compañiaStockContenidoData('.$data["idProducto"].', "'.$data["tipo"].'"); }, "loader-ok")</script>';
                                    }else{
                                        Sistema::debug('error', 'compania.class.php - stockEditarContenido - Hubo un error al editar el contenido del stock. Ref.: '.$idProductoStock);
                                    }
                                }else{
                                    Sistema::debug('error', 'compania.class.php - stockEditarContenido - Hubo un error al recibir el identificador del stock del producto. Ref.: '.$idProductoStock);
                                }
                            }elseif(is_numeric($productoEnStock) && $productoEnStock == 0){
                                $query = DataBase::insert("producto_stock", "producto,sucursal,compañia,".$data["tipo"], "'".$data["idProducto"]."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."','".$data["cantidad"]."'");
                                if($query){
                                    echo '<script>successAction("#producto-'.$data["idProducto"].' #'.$data["tipo"].'", () => { return compañiaStockContenidoData('.$data["idProducto"].', "'.$data["tipo"].'"); }, "loader-ok")</script>';
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
                if(isset($data) && is_array($data) && count($data) == 3){
                    ?>
                    <div id="producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-process" style="display: none"></div>
                    <form id="producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-form" action="./engine/compania/stock-editar-contenido.php" form="#producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-form" process="#producto-<?php echo $data["producto"] ?>-stock-editar-<?php echo $data["tipo"] ?>-process"> 
                        <div class="form-group mb-0"> 
                            <div class="input-group">
                                <input class="form-control form-control-sm" type="number" id="cantidad" name="cantidad" min="0" max="32767" value="<?php echo ($data["cantidad"] > 0) ? $data["cantidad"] : "" ?>">
                                <input class="form-control form-control-sm d-none" type="text" id="tipo" name="tipo" value="<?php echo $data["tipo"] ?>" readonly>
                                <input class="form-control form-control-sm d-none" type="text" id="idProducto" name="idProducto" value="<?php echo $data["producto"] ?>" readonly>
                                <div class="input-group-append">
                                    <button type="button" onclick="compañiaStockEditarContenido(<?php echo $data['producto'] ?>,'<?php echo $data['tipo'] ?>')" class="btn btn-sm btn-outline-success"><i class="fa fa-check"></i></button>
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
                                    compañiaStockEditarContenido(<?php echo $data['producto'] ?>,'<?php echo $data['tipo'] ?>') 
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

        public static function stockFormulario(){
            if(Sistema::usuarioLogueado()){
                $data = Compania::stockData();
                ?>
                <div class="mine-container">
                    <div class="d-flex justify-content-between">
                        <div class="titulo">Stock de productos de <?php echo mb_strtoupper(Compania::getNombre($_SESSION["usuario"]->getCompañia())) ?> - <?php echo Compania::sucursalGetNombre($_SESSION["usuario"]->getSucursal()) ?></div>
                        <button type="button" class="btn btn-info" onclick="compañiaStockRegistroProductoFormulario()"><i class="fa fa-plus"></i> Agregar productos al stock</button>
                    </div> 
                    <div class="p-1"> 
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
                                                ?>
                                                <tr id="producto-<?php echo $value["producto"] ?>" data-key="<?php echo $value["producto"] ?>">
                                                    <th scope="row"><?php echo $producto[$value["producto"]]["codigoBarra"] ?></th>
                                                    <td><?php echo $producto[$value["producto"]]["nombre"] ?></td>
                                                    <td id="stock" data-value="<?php echo (isset($value["stock"]) && is_numeric($value["stock"])) ? $value["stock"] : 0 ?>" class="text-center"><?php echo (isset($value["sucursal"]) && $_SESSION["usuario"]->getSucursal() == $value["sucursal"]) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$value["stock"].'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i> stock inicial</a>" ?></td>
                                                    <td id="minimo" data-value="<?php echo (isset($value["minimo"]) && is_numeric($value["minimo"])) ? $value["minimo"] : 0 ?>" class="text-center"><?php echo (isset($value["minimo"]) && is_numeric($value["minimo"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$value["minimo"].'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="maximo" data-value="<?php echo (isset($value["maximo"]) && is_numeric($value["maximo"])) ? $value["maximo"] : 0 ?>" class="text-center"><?php echo (isset($value["maximo"]) && is_numeric($value["maximo"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$value["maximo"].'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="precio" data-value="<?php echo (isset($value["precio"]) && is_numeric($value["precio"])) ? $value["precio"] : "$0" ?>" class="text-center"><?php echo (isset($value["precio"]) && is_numeric($value["precio"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$'.$value["precio"].'</span> <i class="fa fa-pencil"></i></button>' : '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$0</span> <i class="fa fa-pencil"></i></button>' ?></td>
                                                    <td id="precio_mayorista" data-value="<?php echo (isset($value["precio_mayorista"]) && is_numeric($value["precio_mayorista"])) ? $value["precio_mayorista"] : "$0" ?>" class="text-center"><i class='fa fa-circle text-muted'></i></td>
                                                    <td><?php echo $productoTipo[$producto[$value["producto"]]["tipo"]]; ?></td>
                                                    <td><?php echo $productoCategoria[$producto[$value["producto"]]["categoria"]] ?></td>
                                                </tr>
                                                <?php
                                                $counter++;
                                                if($counter == 500){
                                                    ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center">
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
                                                    </tr>
                                                    <?php
                                                    break;
                                                }
                                            } 
                                        }else{
                                            ?> 
                                            <tr>
                                                <td colspan="9" class="text-center">
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
                                            <td colspan="9" class="text-center">
                                                Hubo un error al encontrar los productos de la compañía. <b>Intente nuevamente o contacte al administrador.</b>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <script> 
                        $(document).ready(function() {
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
                                content: 'Precio al por mayor del artículo. <b>No disponible</b>',
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
                                "lengthMenu": [ [8, 25, 50, 100, -1], [8, 25, 50, 100, "Todos"] ],
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

        public static function buscarFormulario()
        {
            $data = Compania::getCompania(); 
            ?>
            <div> 
                <div class="titulo">Búsqueda de Compañía</div>
                <div id="compania-buscar-process"></div>
                <form id="compania-buscar-form" action="./includes/compania/buscar.php" form="#compania-buscar-form" process="#compania-buscar-process"> 
                    <div class="form-group">
                        <label for="compania"><i class="fa fa-list-alt"></i> Seleccione Compañía</label>
                        <select class="form-control" id="compania" name="compania">
                            <option value=""> -- </option>
                            <?php
                                foreach($data AS $key => $value){
                                    echo '<option value="'.$key.'">'.$value["nombre"].'</option>';
                                }
                            ?>
                        </select>
                        <button type="button" onclick="buscarCompañiaFormulario()" class="btn btn-success">Buscar</button>
                    </div>
                </form>
            </div>  
            <?php 
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