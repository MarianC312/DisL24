<?php
    class Compania{ 
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
                    <div class="titulo">Stock de productos de <?php echo mb_strtoupper(Compania::getNombre($_SESSION["usuario"]->getCompañia())) ?> - <?php echo Compania::sucursalGetNombre($_SESSION["usuario"]->getSucursal()) ?></div>
                    <div class="p-1">
                        <button type="button" class="btn btn-primary" onclick="compañiaStockRegistroProductoFormulario()" style="position: absolute; top: 5px; right: 5px;"><i class="fa fa-plus"></i> Agregar productos al stock</button>
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
                                                    No se encontraron productos registrados en la compañia. Para cargar un nuevo producto clickee en el siguiente <a href="#/" onclick="productoRegistroFormulario()">link</a>.
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