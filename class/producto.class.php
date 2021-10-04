<?php
    class Producto{

        public static function FEChunkLoad($chunk = 0, $force){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $limit = 60000;
                $productoCodificadoCantidad = count($_SESSION["lista"]["producto"]["codificado"]);
                $productoNoCodificadoCantidad = count($_SESSION["lista"]["producto"]["noCodificado"]);
                $productoCantidadMayor = ($productoCodificadoCantidad >= $productoNoCodificadoCantidad) ? $productoCodificadoCantidad : $productoNoCodificadoCantidad;
                $data = [
                    "tipo" => ($force) ? 1 : 0, 
                    "chunk" => [
                        "totales" => ceil(($productoCodificadoCantidad / $limit)),
                        "actual" => $chunk
                    ],
                    "producto" => [
                        "total" => ($productoCodificadoCantidad + $productoNoCodificadoCantidad),
                        "codificado" => [
                            "total" => $productoCodificadoCantidad,
                            "cargado" => 0, 
                            "lista" => []
                        ],
                        "noCodificado" => [
                            "total" => $productoNoCodificadoCantidad,
                            "cargado" => 0, 
                            "lista" => []
                        ]
                    ]
                ];
                for($i = ($chunk * $limit); $i <= ($limit * ($chunk + 1)); $i++){
                    if($i <= $productoCantidadMayor){
                        if($i <= $productoCodificadoCantidad){ 
                            if(array_key_exists($i, $_SESSION["lista"]["producto"]["codificado"])){
                                $idStock = Sistema::buscarProductoIdEnStock($_SESSION["lista"]["producto"]["codificado"][$i]["id"]);
                                if(is_numeric($idStock) && $idStock >= 0){
                                    $data["producto"]["codificado"]["cargado"]++;
                                    $data["producto"]["codificado"]["lista"][$i]["data"] = $_SESSION["lista"]["producto"]["codificado"][$i]; 
                                    $data["producto"]["codificado"]["lista"][$i]["stock"] = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"][$idStock]; 
                                }
                            }
                        }
                        if($i <= $productoNoCodificadoCantidad){
                            if(array_key_exists($i, $_SESSION["lista"]["producto"]["noCodificado"])){
                                $idStock = Sistema::buscarProductoIdEnStock($_SESSION["lista"]["producto"]["noCodificado"][$i]["id"], "productoNC");
                                if(is_numeric($idStock) && $idStock >= 0){
                                    $data["producto"]["noCodificado"]["cargado"]++;
                                    $data["producto"]["noCodificado"]["lista"][$i]["data"] = $_SESSION["lista"]["producto"]["noCodificado"][$i];
                                    $data["producto"]["noCodificado"]["lista"][$i]["stock"] = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"][$idStock]; 
                                } 
                            }
                        }
                    }else{
                        break;
                    }
                }
                return $data;
            }else{
                Sistema::debug('error', 'producto.class.php - FEChunkLoad - Usuario no logueado.');
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
                        Sistema::debug('error', 'producto.class.php - stockCorroboraExistencia - Error al comprobar la existencia del stock.');
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - stockCorroboraExistencia - Identificador de producto incorrecto. Ref.: '.$idProducto); 
                }
            }else{
                Sistema::debug('error', 'producto.class.php - stockCorroboraExistencia - Usuario no logueado.');
            }
            return false;
        }

        

        public static function stockCompañiaGetData($idSucursal = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("producto_stock", "*", "compañia = '".$_SESSION["usuario"]->getCompañia()."' ".((is_numeric($idSucursal)) ? "AND sucursal = '".$idSucursal."'" : "")."", "");
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
                    Sistema::debug('error', 'producto.class.php - stockCompañia - Error al buscar información de stock de compañía. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('', ' - Usuario no logueado.');
            }
            return false;
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
                            Sistema::debug('info', 'producto.class.php - stockGetId - No se encontró el producto en stock.');
                        }
                    }else{
                        Sistema::debug('error', 'producto.class.php - stockGetId - Error al comprobar la existencia del stock.');
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - stockGetId - Identificador de producto incorrecto. Ref.: '.$idProducto);
                }
            }else{
                Sistema::debug('error', 'producto.class.php - stockGetId - Usuario no logueado.');
            }
            return false;
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
                            Sistema::debug('error', 'producto.class.php - stockGetData - No se encontró la información del stock del producto. Ref.: '.$idProducto);
                            return 0;
                        }
                    }else{
                        Sistema::debug('error', 'producto.class.php - stockGetData - Hubo un error al buscar la información del stock del producto. Ref.: '.$idProducto);
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - stockGetData - Error en el identificador de producto o tipo de dato. Ref.: [ID => '.$idProducto.', TIPO => '.$tipo.']');
                }
            }else{
                Sistema::debug('error', 'producto.class.php - stockGetData - Usuario no logueado.');
            }
            return false;
        }

        public static function inventarioContenido($idProducto, $tipo){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0 && isset($tipo) && strlen($tipo) > 0){
                    $data = Producto::stockGetData($idProducto, $tipo);
                    if(is_numeric($data)){
                        echo '<button type="button" class="btn btn-sm btn-link btn-iconed p-0"><span class="spn">'.$data.'</span> <i class="fa fa-pencil"></i></button>';
                        ?>
                        <script>
                            $(document).ready(() => {
                                $('#producto-<?php echo $idProducto ?> #<?php echo $tipo ?> button').on('click', (e) => {
                                    productoInventarioEditarContenidoFormulario(<?php echo $idProducto ?>,e.currentTarget.parentNode.getAttribute("id"),<?php echo $data ?>);
                                });
                            })
                        </script>
                        <?php
                    }else{
                        Sistema::debug('error', 'producto.class.php - inventarioContenido - Información de stock erronea. Ref.: '.$data);
                        echo '<button onclick="successAction("#producto-'.$idProducto.' #'.$tipo.'", null, "loader-ok")" class="btn btn-info"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - inventarioContenido - Error en el identificador de producto o tipo de dato. Ref.: [ID => '.$idProducto.', TIPO => '.$tipo.']');
                    echo '<button onclick="successAction("#producto-'.$idProducto.' #'.$tipo.'", null, "loader-ok")" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                }
            }else{
                Sistema::debug('error', 'producto.class.php - inventarioContenido - Usuario no logueado.');
            }
        }

        public static function editarContenidoFormularioRegistro($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    echo Sistema::loading();
                    Session::iniciar();
                    $query = DataBase::update(($data["productoTipo"] == "codificado") ? "producto" : "compañia_producto", $data["tipo"]." = '".$data["valor"]."', operador = '".$_SESSION["usuario"]->getId()."'", "id = '".$data["idProducto"]."'");
                    if($query){
                        echo '<script>successAction("#producto-'.$data["idProducto"].' #'.$data["tipo"].'", () => { return productoContenido('.$data["idProducto"].', "'.$data["tipo"].'", "'.$data["productoTipo"].'"); }, "loader-ok")</script>';
                    }else{
                        Sistema::debug('error', 'producto.class.php - editarContenidoFormularioRegistro - Error al actualizar los datos del producto. Ref.: '.DataBase::getError());
                        echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-info"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - editarContenidoFormularioRegistro - Error en arreglo de datos recibido. Ref.: '.count($data));
                    echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                }
            }else{
                Sistema::debug('error', 'producto.class.php - editarContenidoFormularioRegistro - Usuario no logueado.');
            }
        }

        public static function contenido($idProducto, $tipo, $productoTipo = "codificado"){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0 && isset($tipo) && strlen($tipo) > 0){
                    $data = ($productoTipo == "codificado") ? Producto::getData($idProducto) : Compania::productoNoCodifData($idProducto);
                    if($productoTipo == "noCodificado"){
                        $data = $data[$idProducto];
                    }
                    if(is_array($data) && count($data) > 0){
                        echo '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$data[$tipo].'</span> <i class="fa fa-pencil"></i></button>';
                        ?>
                        <script>
                            $(document).ready(() => {
                                console.log('#producto-<?php echo $idProducto ?> #<?php echo $tipo ?> button');
                                $('#producto-<?php echo $idProducto ?> #<?php echo $tipo ?> button').on('click', (e) => {
                                    productoEditarContenidoFormulario(<?php echo $idProducto ?>, '<?php echo $tipo ?>','<?php echo $data[$tipo] ?>','<?php echo $productoTipo ?>');
                                });
                            })
                        </script>
                        <?php
                    }else{
                        Sistema::debug('error', 'producto.class.php - stockContenido - Información de stock erronea. Ref.: '.$data[$tipo]);
                        echo '<button onclick="successAction(\'#producto-'.$idProducto.' #'.$tipo.'\', () => { compañiaStockContenidoData('.$idProducto.', \''.$tipo.'\') })" class="btn btn-info"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - stockContenido - Error en el identificador de producto o tipo de dato. Ref.: [ID => '.$idProducto.', TIPO => '.$tipo.']');
                    echo '<button onclick="successAction(\'#producto-'.$idProducto.' #'.$tipo.'\', () => { compañiaStockContenidoData('.$idProducto.', \''.$tipo.'\') })" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                }
            }else{
                Sistema::debug('error', 'producto.class.php - stockContenido - Usuario no logueado.');
            }
        }

        public static function inventarioEditarContenido($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    echo Sistema::loading();
                    $codigoProducto = Producto::getCodigo($data["idProducto"]);
                    if(is_numeric($codigoProducto) && !is_bool($codigoProducto)){
                        if(Producto::corroboraExistencia(["codigo" => $codigoProducto])){
                            $productoEnStock = Producto::stockCorroboraExistencia($data["idProducto"]);
                            Session::iniciar();
                            if(is_bool($productoEnStock) && $productoEnStock){
                                $idProductoStock = Producto::stockGetId($data["idProducto"]);
                                if(is_numeric($idProductoStock) && $idProductoStock > 0){
                                    $query = DataBase::update("producto_stock", $data["tipo"]." = ".$data["cantidad"], "id = '".$idProductoStock."' AND producto = '".$data["idProducto"]."' AND sucursal = '".$_SESSION["usuario"]->getSucursal()."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                                    if($query){
                                        echo '<script>successAction("#producto-'.$data["idProducto"].' #'.$data["tipo"].'", () => { return productoInventarioContenidoData('.$data["idProducto"].', "'.$data["tipo"].'"); }, "loader-ok")</script>';
                                    }else{
                                        Sistema::debug('error', 'producto.class.php - inventarioEditarContenido - Hubo un error al editar el contenido del stock. Ref.: '.$idProductoStock);
                                    }
                                }else{
                                    Sistema::debug('error', 'producto.class.php - inventarioEditarContenido - Hubo un error al recibir el identificador del stock del producto. Ref.: '.$idProductoStock);
                                }
                            }elseif(is_numeric($productoEnStock) && $productoEnStock == 0){
                                $query = DataBase::insert("producto_stock", "producto,sucursal,compañia,".$data["tipo"], "'".$data["idProducto"]."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."','".$data["cantidad"]."'");
                                if($query){
                                    echo '<script>successAction("#producto-'.$data["idProducto"].' #'.$data["tipo"].'", () => { return productoInventarioContenidoData('.$data["idProducto"].', "'.$data["tipo"].'"); }, "loader-ok")</script>';
                                }else{
                                    Sistema::debug('error', 'producto.class.php - inventarioEditarContenido - Hubo un error al registrar '.$data["tipo"].' del producto. Ref.: '.$codigoProducto);
                                }
                            }else{
                                Sistema::debug('info', 'producto.class.php - inventarioEditarContenido - No se pudo comprobar la existencia de stock del producto. Ref.: '.$codigoProducto);
                                echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-info"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                            }
                        }else{
                            Sistema::debug('info', 'producto.class.php - inventarioEditarContenido - Producto inexistente. Ref.: '.$codigoProducto);
                            echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-info"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                        }
                    }else{ 
                        Sistema::debug('error', 'producto.class.php - inventarioEditarContenido - Código de producto incorrecto. Ref.: '.$codigoProducto);
                        echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - inventarioEditarContenido - Arreglo de datos incorrecto.');
                    echo '<button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> Reintentar</button>';
                }
            }else{
                Sistema::debug('error', 'producto.class.php - inventarioEditarContenido - Usuario no logueado.');
            }
        }

        public static function inventarioEditarContenidoFormulario($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) == 3){
                    ?>
                    <div id="producto-<?php echo $data["producto"] ?>-inventario-editar-<?php echo $data["tipo"] ?>-process" style="display: none"></div>
                    <form id="producto-<?php echo $data["producto"] ?>-inventario-editar-<?php echo $data["tipo"] ?>-form" action="./engine/producto/inventario-editar-contenido.php" form="#producto-<?php echo $data["producto"] ?>-inventario-editar-<?php echo $data["tipo"] ?>-form" process="#producto-<?php echo $data["producto"] ?>-inventario-editar-<?php echo $data["tipo"] ?>-process"> 
                        <div class="form-group mb-0"> 
                            <div class="input-group">
                                <input class="form-control form-control-sm" type="number" id="cantidad" name="cantidad" min="0" max="32767" value="<?php echo $data["cantidad"] ?>">
                                <input class="form-control form-control-sm d-none" type="text" id="tipo" name="tipo" value="<?php echo $data["tipo"] ?>" readonly>
                                <input class="form-control form-control-sm d-none" type="text" id="idProducto" name="idProducto" value="<?php echo $data["producto"] ?>" readonly>
                                <div class="input-group-append">
                                    <button type="button" onclick="productoInventarioEditarContenido(<?php echo $data['producto'] ?>,'<?php echo $data['tipo'] ?>')" class="btn btn-sm btn-outline-success"><i class="fa fa-check"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <script>
                        $(document).ready(() => {
                            $("#producto-<?php echo $data["producto"] ?>-inventario-editar-<?php echo $data["tipo"] ?>-form #cantidad").focus();
                            $("#producto-<?php echo $data["producto"] ?>-inventario-editar-<?php echo $data["tipo"] ?>-form #cantidad").keypress((e) => {
                                var keycode = (e.keyCode ? e.keyCode : e.which);
                                if(keycode == '13'){
                                    productoInventarioEditarContenido(<?php echo $data['producto'] ?>,'<?php echo $data['tipo'] ?>') 
                                }
                            });
                        })
                    </script>
                    <?php
                }else{
                    Sistema::debug('error', 'producto.class.php - inventarioEditarContenidoFormulario - Error en arreglo de datos.');
                }
            }else{
                Sistema::debug('error', 'producto.class.php - inventarioEditarContenidoFormulario - Usuario no logueado.');
            }
        }

        public static function inventario(){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $data = Producto::data();
                ?>
                <div class="mine-container">
                    <div class="titulo">Inventario de <?php echo mb_strtoupper(Compania::getNombre($_SESSION["usuario"]->getCompañia())) ?> - <?php echo Compania::sucursalGetNombre($_SESSION["usuario"]->getSucursal()) ?></div>
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
                                                ?>
                                                <tr id="producto-<?php echo $key ?>" data-key="<?php echo $key ?>">
                                                    <th scope="row"><?php echo $value["codigoBarra"] ?></th>
                                                    <td><?php echo $value["nombre"] ?></td>
                                                    <td id="stock" data-value="<?php echo (isset($value["stock"]) && is_numeric($value["stock"])) ? $value["stock"] : 0 ?>" class="text-center"><?php echo (isset($value["sucursal"]) && $_SESSION["usuario"]->getSucursal() == $value["sucursal"]) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$value["stock"].'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i> stock inicial</a>" ?></td>
                                                    <td id="minimo" data-value="<?php echo (isset($value["minimo"]) && is_numeric($value["minimo"])) ? $value["minimo"] : 0 ?>" class="text-center"><?php echo (isset($value["minimo"]) && is_numeric($value["minimo"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$value["minimo"].'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="maximo" data-value="<?php echo (isset($value["maximo"]) && is_numeric($value["maximo"])) ? $value["maximo"] : 0 ?>" class="text-center"><?php echo (isset($value["maximo"]) && is_numeric($value["maximo"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">'.$value["maximo"].'</span> <i class="fa fa-pencil"></i></button>' : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="precio" data-value="<?php echo (isset($value["precio"]) && is_numeric($value["precio"])) ? $value["precio"] : "$0" ?>" class="text-center"><?php echo (isset($value["precio"]) && is_numeric($value["precio"])) ? '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$'.$value["precio"].'</span> <i class="fa fa-pencil"></i></button>' : '<button type="button" class="btn btn-sm btn-link btn-iconed"><span class="spn">$0</span> <i class="fa fa-pencil"></i></button>' ?></td>
                                                    <td id="precio_mayorista" data-value="<?php echo (isset($value["precio_mayorista"]) && is_numeric($value["precio_mayorista"])) ? $value["precio_mayorista"] : "$0" ?>" class="text-center"><i class='fa fa-circle text-muted'></i></td>
                                                    <td><?php echo $productoTipo[$value["tipo"]]; ?></td>
                                                    <td><?php echo $productoCategoria[$value["categoria"]] ?></td>
                                                    <td><?php echo (is_numeric($value["subcategoria"])) ? $productoSubcategoria[$value["subcategoria"]] : "<span class='text-muted'>No categorizado</span>" ?></td>
                                                    <td></td>
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
                Sistema::debug('error', 'producto.class.php - inventario - Usuario no logueado.');
            }
        }

        public static function getData($idProducto, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    Session::iniciar();
                    $query = DataBase::select("producto", "*", "id = '".$idProducto."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $data = DataBase::getArray($query);
                            $stock = Producto::stockGetData($idProducto, "stock,minimo,maximo");
                            if(is_array($stock)){
                                foreach($stock AS $key => $value){
                                    $data[$key] = $value;
                                }
                            }else{
                                Sistema::debug('error', 'producto.class.php - getData - Error al recibir la información de stock del producto. Ref.: '.$idProducto);
                            }
                            foreach($data AS $key => $value){
                                if(is_int($key)){
                                    unset($data[$key]);
                                }
                            }
                            return $data;
                        }else{
                            Sistema::debug('error', 'producto.class.php - getData - No se encontraron productos relacionados al identificador. Ref.: '.$idProducto);
                        }
                    }else{
                        Sistema::debug('error', 'producto.class.php - getData - Error al consultar la información del producto. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - getData - Identificador de producto erroneo. Ref.: '.$idProducto);
                }
            }else{
                Sistema::debug('error', 'producto.class.php - getData - Usuario no logueado.');
            }
            return false;
        }

        public static function buscadorData($data, $max = 500){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
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
                                $query = DataBase::select("producto", "*", $cond, "ORDER BY codigoBarra ASC, nombre ASC LIMIT ".$max);
                            break;
                            case 2:
                                $query = DataBase::select("producto", "*", "codigoBarra LIKE '".$data["codigo"]."%'", "ORDER BY codigoBarra ASC, nombre ASC LIMIT ".$max);
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
                            Sistema::debug('error', 'compania.class.php - stockRegistroProductoListaFormulario - Error al buscar los productos. Ref.: '.DataBase::getError());
                        }
                    }else{
                        Sistema::debug('error', 'compania.class.php - stockRegistroProductoListaFormulario - Error en el dato de búsqueda recibido. Ref.: '.$data["busqueda"]);
                    }
                }else{
                    Sistema::debug('error', 'compania.class.php - stockRegistroProductoListaFormulario - Error al recibir el arreglo de datos.');
                }
            }else{
                Sistema::debug('error', 'producto.class.php - buscadorData - Usuario no logueado.');
            }
            return false;
        }

        public static function data(){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                return $_SESSION["lista"]["producto"];
            }else{
                Sistema::debug('error', 'producto.class.php - data - Usuario no logueado.');
            }
        }

        public static function getCodigo($idProducto){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    $query = DataBase::select("producto", "codigoBarra", "id = '".$idProducto."'", "");
                    if($query){
                        if(DataBase::getNumRows($query) == 1){
                            $dataQuery = DataBase::getArray($query);
                            return $dataQuery["codigoBarra"];
                        }else{
                            Sistema::debug('error', 'producto.class.php - getCodigo - No se encontró el producto. Ref.: '.DataBase::getNumRows($query));
                        }
                    }else{
                        Sistema::debug('error', 'producto.class.php - getCodigo - Hubo un error al buscar la información del producto. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - getCodigo - Hubo un error con el valor del identificador recibido. Ref.: '.$idProducto);
                }
            }else{
                Sistema::debug('error', 'producto.class.php - getCodigo - Usuario no logueado.');
            }
            return false;
        }

        public static function corroboraExistencia($data, $cargaFormularioRegistro = false){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && (count($data) == 4 || count($data) == 1)){
                    if(isset($data["codigo"]) && is_numeric($data["codigo"]) && $data["codigo"] > 0){
                        Session::iniciar();
                        $query = DataBase::select("producto", "id", "codigoBarra = '".$data["codigo"]."'", "");
                        if($query){
                            if(DataBase::getNumRows($query) == 1){
                                $dataQuery = DataBase::getArray($query);
                                if($cargaFormularioRegistro){
                                    Sistema::debug("success", "producto.class.php - corroboraExistencia - Producto encontrado, carga de formulario de edición para producto ID: ".$dataQuery["id"].".");
                                    echo '<script>productoEditarFormulario('.$dataQuery["id"].')</script>';
                                }else{
                                    Sistema::debug("success", "producto.class.php - corroboraExistencia - Producto encontrado ID: ".$dataQuery["id"].".");
                                    return true;
                                }
                            }else{ 
                                if($cargaFormularioRegistro){
                                    echo '<script>productoRegistroFormulario(0, "'.$data["codigo"].'")</script>';
                                    Sistema::debug("success", "producto.class.php - corroboraExistencia - Producto inexistente, cargando formulario de registro.");
                                }else{
                                    Sistema::debug("success", "producto.class.php - corroboraExistencia - Producto inexistente.");
                                    return false;
                                }
                            }
                        }else{
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al comprobar la información del producto. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                            Sistema::debug("error", "producto.class.php - corroboraExistencia - Error en query de comprobación de información.");
                        }
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'El código ingresado es incorrecto. Debe ser un número. <b>Intente nuevamente.</b>';
                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                        Alert::mensaje($mensaje);
                        Sistema::debug("error", "producto.class.php - corroboraExistencia - Código no numérico.");
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información del producto. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug("error", "producto.class.php - corroboraExistencia - No se recibió la data correcta.");
                }
            }else{
                Sistema::debug('error', 'producto.class.php - corroboraExistencia - Usuario no logueado.');
            }
        }

        public static function nocodifCorroboraExistencia($data, $cargaFormularioRegistro = false, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && (count($data) <= 4 && count($data) >= 1)){
                    if(isset($data["codigo"]) && ((is_numeric($data["codigo"]) && $data["codigo"] > 0) || (is_numeric($data["idProducto"]) && $data["idProducto"] > 0))){
                        Session::iniciar();
                        if(is_numeric($data["codigo"]) && $data["codigo"] > 0){
                            $condicion =  "codigoBarra = '".$data["codigo"]."'";
                        }elseif(is_numeric($data["idProducto"]) && $data["idProducto"] > 0){
                            $condicion =  "id = '".$data["idProducto"]."'";
                        }else{
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al recibir la información del producto. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                            Sistema::debug("error", "producto.class.php - nocodifCorroboraExistencia - Valor de código e identificador de producto incorrecto.");
                            return null;
                        }
                        $query = DataBase::select("compañia_producto", "id", $condicion." AND compañia = ".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia()), "");
                        if($query){
                            if(DataBase::getNumRows($query) == 1){
                                $dataQuery = DataBase::getArray($query);
                                if($cargaFormularioRegistro){
                                    Sistema::debug("success", "producto.class.php - nocodifCorroboraExistencia - Producto encontrado, carga de formulario de edición para producto ID: ".$dataQuery["id"].".");
                                    echo '<script>productoEditarFormulario('.$dataQuery["id"].')</script>';
                                }else{
                                    Sistema::debug("success", "producto.class.php - nocodifCorroboraExistencia - Producto encontrado ID: ".$dataQuery["id"].".");
                                    return true;
                                }
                            }else{ 
                                if($cargaFormularioRegistro){
                                    echo '<script>productoRegistroFormulario(0, "'.$data["codigo"].'")</script>';
                                    Sistema::debug("success", "producto.class.php - nocodifCorroboraExistencia - Producto inexistente, cargando formulario de registro.");
                                }else{
                                    Sistema::debug("success", "producto.class.php - nocodifCorroboraExistencia - Producto inexistente.");
                                    return false;
                                }
                            }
                        }else{
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al comprobar la información del producto. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                            Sistema::debug("error", "producto.class.php - nocodifCorroboraExistencia - Error en query de comprobación de información.");
                        }
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'El código ingresado es incorrecto. Debe ser un número. <b>Intente nuevamente.</b>';
                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                        Alert::mensaje($mensaje);
                        Sistema::debug("error", "producto.class.php - nocodifCorroboraExistencia - Código no numérico.");
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información del producto. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug("error", "producto.class.php - nocodifCorroboraExistencia - No se recibió la data correcta.");
                }
            }else{
                Sistema::debug('error', 'producto.class.php - nocodifCorroboraExistencia - Usuario no logueado.');
            }
        }

        public static function corroboraExistenciaFormulario(){
            if(Sistema::usuarioLogueado()){
                ?>
                <div class="mine-container">
                    <div class="titulo">Corroborar existencia</div>
                    <div class="d-flex justify-content-center align-items-center">
                        <img id="barcode" />
                    </div>
                    <div id="producto-corrobora-existencia-process" style="display: none"></div>
                    <form id="producto-corrobora-existencia-form" action="./engine/producto/corrobora-existencia.php" form="#producto-corrobora-existencia-form" process="#producto-corrobora-existencia-process"> 
                        <div class="form-group">
                            <label class="col-form-label" for="codigo"><i class="fa fa-barcode"></i> Código</label>
                            <input type="text" class="form-control" placeholder="Ingrese el código comercial del producto" id="codigo" name="codigo" autocomplete="off">
                        </div>
                        <div class="form-group d-none">
                            <label class="col-form-label" for="formulario">Carga formulario de registro</label>
                            <input type="text" class="form-control" placeholder="Ingrese código del producto" id="formulario" name="formulario" value="1" readonly>
                        </div>
                        <div class="form-group">
                            <button type="button" onclick="productoCorroboraExistencia()" class="btn btn-success">Corroborar <span class="d-inline" style="color: inherit" id="producto-corrobora-cantidad"></span></button>
                        </div>
                    </form>
                </div>
                <script>
                    $("#codigo").on('keyup', () => {
                        barCode("#barcode", $("#codigo").val());
                    })
                </script>
                <?php
            }else{
                Sistema::debug('error', 'producto.class.php - corroboraExistenciaFormulario - Usuario no logueado.');
            }
        }

        public static function registro($data){
            //echo '<button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-outline-info">Regresar</button>'; 
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    $data["nombre"] = Sistema::textoSinSignos(mb_strtoupper(Sistema::textoSinAcentos(trim($data["nombre"]))));
                    $productoExiste = Producto::corroboraExistencia(["codigo" => $data["codigo"]]);
                    if($productoExiste){
                        Sistema::debug("info", "producto.class.php - registro - El producto ya existe en la base de datos.");
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'El producto ya se encuentra registrado.';
                        $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                            <button type="button" onclick="compañiaStock()" class="btn btn-info">Ir a Mi Stock</button>
                            <button type="button" onclick="compañiaStockRegistroProductoFormulario()" class="btn btn-outline-info">Ir a Agregar Producto a Mi Stock</button>
                        </div>';
                        Alert::mensaje($mensaje);
                        return;
                    }else{
                        $query = DataBase::insert("producto", "nombre,tipo,codigoBarra,categoria,subcategoria,operador", "'".$data["nombre"]."','".$data["tipo"]."','".$data["codigo"]."','".$data["categoria"]."',".((isset($data["subcategoria"]) && is_numeric($data["subcategoria"])) ? $data["subcategoria"] : "NULL").",'".$_SESSION["usuario"]->getId()."'");
                        if($query){ 
                            $aCargar = ["stock","minimo","maximo","precio","precioMayorista","precioKiosco"];
                            $cargaStock = false;
                            foreach($aCargar AS $key => $value){
                                if(is_numeric($data[$value]) && $data[$value] >= 0){
                                    $cargaStock = true;
                                }
                            }
                            Session::iniciar();
                            $_SESSION["usuario"]->tareaEliminar('Registro de producto ['.$data["codigo"].']');
                            Sistema::debug("success", "producto.class.php - registro - Producto registrado satisfactoriamente.");
                            $mensaje['tipo'] = 'success';
                            $mensaje['cuerpo'] = 'Se registró el producto <b>'.$data["nombre"].'</b> satisfactoriamente.';
                            if($cargaStock){
                                $data["idProducto"] = DataBase::getLastId();
                                $stockRegistro = Compania::stockRegistro($data);
                                if(!$stockRegistro){
                                    $mensaje['cuerpo'] .= '<br><br> <b>¡Advertencia! Hubo un error al registrar el stock del producto.</b> <small>(Regresá a <a href="#"  onclick="compañiaStock()">"Mi Stock"</a> para cargar los datos.)</small> <br>';
                                }
                            }
                            $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                                <button type="button" onclick="compañiaStock()" class="btn btn-success">Ir a Mi Stock</button>
                                <button type="button" onclick="compañiaStockRegistroProductoFormulario()" class="btn btn-outline-success">Ir a Agregar Producto a Mi Stock</button>
                            </div>';
                            Alert::mensaje($mensaje);
                            return true;
                        }else{
                            Sistema::debug('error', 'producto.class.php - registro - Error en query de registro de producto.');
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al registrar el producto. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block"><button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                            return false;
                        }
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug("error", "producto.class.php - registro - Arreglo de datos del formulario incorrecto.");
                }
            }else{
                Sistema::debug('error', 'producto.class.php - registro - Usuario no logueado.');
            }
        }

        public static function nocodifRegistro($data){
            //echo '<button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-outline-info">Regresar</button>'; 
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    $data["nombre"] = Sistema::textoSinSignos(mb_strtoupper(Sistema::textoSinAcentos(trim($data["nombre"]))));
                    $productoExiste = (is_null($data["codigo"]) || strlen($data["codigo"]) == 0) ? false : Producto::nocodifCorroboraExistencia(["codigo" => $data["codigo"]]);
                    if($productoExiste){
                        Sistema::debug("info", "producto.class.php - registro - El producto ya existe en la base de datos.");
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'El producto ya se encuentra registrado.';
                        $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                            <button type="button" onclick="compañiaStock()" class="btn btn-info">Ir a Mi Stock</button>
                            <button type="button" onclick="compañiaStockRegistroProductoFormulario()" class="btn btn-outline-info">Ir a Agregar Producto a Mi Stock</button>
                        </div>';
                        Alert::mensaje($mensaje);
                        return;
                    }else{
                        $query = DataBase::insert("compañia_producto", "nombre,tipo,codigoBarra,categoria,subcategoria,operador,compañia", "'".$data["nombre"]."','".$data["tipo"]."',".((is_null($data["codigo"]) || strlen($data["codigo"]) == 0) ? "NULL" : "'".$data["codigo"]."'").",'".$data["categoria"]."',".((isset($data["subcategoria"]) && is_numeric($data["subcategoria"])) ? $data["subcategoria"] : "NULL").",'".$_SESSION["usuario"]->getId()."','".$_SESSION["usuario"]->getCompañia()."'");
                        if($query){ 
                            $aCargar = ["stock","minimo","maximo","precio","precioMayorista","precioKiosco"];
                            $cargaStock = false;
                            foreach($aCargar AS $key => $value){
                                if(is_numeric($data[$value]) && $data[$value] >= 0){
                                    $cargaStock = true;
                                }
                            }
                            Session::iniciar();
                            Sistema::debug("success", "producto.class.php - registro - Producto registrado satisfactoriamente.");
                            $mensaje['tipo'] = 'success';
                            $mensaje['cuerpo'] = 'Se registró el producto <b>'.$data["nombre"].'</b> satisfactoriamente.';
                            if($cargaStock){
                                $data["idProducto"] = DataBase::getLastId();
                                $stockRegistro = Compania::stockRegistro($data, false, false);
                                if(!$stockRegistro){
                                    $mensaje['cuerpo'] .= '<br><br> <b>¡Advertencia! Hubo un error al registrar el stock del producto.</b> <small>(Regresá a <a href="#"  onclick="compañiaStock()">"Mi Stock"</a> para cargar los datos.)</small> <br>';
                                }
                            }
                            $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                                <button type="button" onclick="compañiaStock()" class="btn btn-success">Ir a Mi Stock</button>
                                <button type="button" onclick="compañiaStockRegistroProductoFormulario()" class="btn btn-outline-success">Ir a Agregar Producto a Mi Stock</button>
                            </div>';
                            Alert::mensaje($mensaje);
                            return true;
                        }else{
                            Sistema::debug('error', 'producto.class.php - nocodifRegistro - Error en query de registro de producto.');
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al registrar el producto. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block"><button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                            return false;
                        }
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug("error", "producto.class.php - nocodifRegistro - Arreglo de datos del formulario incorrecto.");
                }
            }else{
                Sistema::debug('error', 'producto.class.php - nocodifRegistro - Usuario no logueado.');
            }
        }

        public static function editarFormulario($idProducto){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    $codigoProducto = Producto::getCodigo($idProducto);
                    if(is_numeric($codigoProducto) && $codigoProducto > 0){
                        $data = Producto::getData($idProducto);
                        echo '<pre>';
                        print_r($data);
                        echo '</pre>';
                        ?>
                        <div class="mine-container">
                            <div class="titulo">Edición de producto: <?php echo $data["nombre"] ?></div>
                            <div id="producto-registro-debug"></div>
                            <div id="producto-registro-stepper-1" class="bs-stepper">
                                <div class="bs-stepper-header" role="tablist">
                                    <div class="step <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 1) ? "active" : ""; ?>" data-target="#producto-registro-p-1">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-1" id="producto-registro-p-1-trigger" aria-selected="<?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 1) ? "true" : "false"; ?>">
                                            <span class="bs-stepper-circle">1</span>
                                            <span class="bs-stepper-label">Datos básicos</span>
                                        </button>
                                    </div>
                                    <div class="line"></div>
                                    <div class="step <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 2) ? "active" : ""; ?>" data-target="#producto-registro-p-2">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-2" id="producto-registro-p-2-trigger" aria-selected="<?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 2) ? "true" : "false"; ?>">
                                            <span class="bs-stepper-circle">2</span>
                                            <span class="bs-stepper-label">Datos opcionales</span>
                                        </button>
                                    </div>
                                    <div class="line"></div>
                                    <div class="step <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 3) ? "active" : ""; ?>" data-target="#producto-registro-p-3">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-2" id="producto-registro-p-2-trigger" aria-selected="<?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 3) ? "true" : "false"; ?>">
                                            <span class="bs-stepper-circle">2</span>
                                            <span class="bs-stepper-label">Stock</span>
                                        </button>
                                    </div>
                                    <div class="line"></div>
                                    <div class="step <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 4) ? "active" : ""; ?>" data-target="#producto-registro-p-4">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-4" id="producto-registro-p-4-trigger" aria-selected="<?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 4) ? "true" : "false"; ?>">
                                            <span class="bs-stepper-circle">4</span>
                                            <span class="bs-stepper-label">Completar modificación</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="bs-stepper-content"> 
                                    <div id="producto-registro-formulario-process" style="display: none;"></div>
                                    <form id="producto-registro-formulario-form" onsubmit="return false" action="./engine/producto/editar.php" form="#producto-editar-formulario-form" process="#producto-registro-formulario-process"> 
                                        <div id="producto-registro-p-1" class="content <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 1) ? "dstepper-block active" : ""; ?>" role="tabpanel" aria-labelledby="producto-registro-p-1-trigger">
                                            <div class="form-group">
                                                <label class="col-form-label required" for="codigo"><i class="fa fa-barcode"></i> Código</label>
                                                <input type="text" class="form-control" required placeholder="Código comercial del producto" id="codigo" name="codigo" value="<?php echo (isset($data["codigo"])) ? $data["codigo"] : ''; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label required" for="tipo"><i class="fa fa-list"></i> Tipo</label>
                                                <div class="input-group">
                                                    <select class="form-control" required id="tipo" name="tipo">
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["producto"]["tipo"]) && count($_SESSION["lista"]["producto"]["tipo"]) > 0){
                                                                foreach($_SESSION["lista"]["producto"]["tipo"] AS $key => $value){
                                                                    $selected = (isset($data["tipo"]) && $data["tipo"] == $key) ? "selected" : "";
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["producto"]["tipo"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de tipos de producto es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["producto"]["tipo"]) && !$_SESSION["lista"]["producto"]["tipo"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de tipos de producto es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de tipos de producto es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-tipo-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div> 
                                            <div class="form-group">
                                                <label class="col-form-label required" for="categoria"><i class="fa fa-list"></i> Categoría</label>
                                                <div class="input-group">
                                                    <select class="form-control" required id="categoria" name="categoria">
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["producto"]["categoria"]) && count($_SESSION["lista"]["producto"]["categoria"]) > 0){
                                                                foreach($_SESSION["lista"]["producto"]["categoria"] AS $key => $value){
                                                                    $selected = (isset($data["categoria"]) && $data["categoria"] == $key) ? "selected" : "";
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["producto"]["categoria"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de categorías de producto es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["producto"]["categoria"]) && !$_SESSION["lista"]["producto"]["categoria"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de categorías de producto es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de categorías de producto es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-categoria-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label required" for="nombre"><i class="fa fa-pencil-square-o"></i> Nombre</label>
                                                <input type="text" class="form-control" required placeholder="Nombre del producto" id="nombre" name="nombre" value="<?php echo (isset($data["nombre"])) ? $data["nombre"] : ""; ?>">
                                            </div>
                                            <div class="form-group d-flex justify-content-end">
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="2" onclick="stepper1.next()">Siguiente</button>
                                            </div>
                                        </div>
                                        
                                        <div id="producto-registro-p-2" class="content <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 2) ? "dstepper-block active" : ""; ?>" role="tabpanel" aria-labelledby="producto-registro-p-2-trigger"> 
                                            <div class="form-group">
                                                <label class="col-form-label" for="subcategoria"><i class="fa fa-list"></i> Sub-Categoría</label>
                                                <div class="input-group">
                                                    <select class="form-control" id="subcategoria" name="subcategoria">
                                                        <option value=""> -- </option>
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["producto"]["subcategoria"]) && count($_SESSION["lista"]["producto"]["subcategoria"]) > 0){
                                                                foreach($_SESSION["lista"]["producto"]["subcategoria"] AS $key => $value){
                                                                    $selected = (isset($data["subcategoria"]) && $data["subcategoria"] == $key) ? "selected" : "";
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["producto"]["fabricante"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de subcagetorias de producto es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["producto"]["fabricante"]) && !$_SESSION["lista"]["producto"]["fabricante"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de subcagetorias de producto es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de subcagetorias de producto es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-subcategoria-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="fabricante"><i class="fa fa-list"></i> Fabricante</label>
                                                <div class="input-group">
                                                    <select class="form-control" id="fabricante" name="fabricante">
                                                        <option value=""> -- </option>
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["fabricante"]) && count($_SESSION["lista"]["fabricante"]) > 0){
                                                                foreach($_SESSION["lista"]["fabricante"] AS $key => $value){
                                                                    $selected = (isset($data["fabricante"]) && $data["fabricante"] == $key) ? "selected" : "";
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["fabricante"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de fabricantes es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["fabricante"]) && !$_SESSION["lista"]["fabricante"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de fabricantes es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de fabricantes es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-categoria-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-around"> 
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="venta" name="venta" value="1" <?php echo (isset($data["venta"]) && $data["venta"] == 1) ? "checked" : ""; ?>>
                                                        <label class="custom-control-label" for="venta">Producto para Venta</label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="compra" name="compra" value="1" <?php echo (isset($data["compra"]) && $data["compra"] == 1) ? "checked" : ""; ?>>
                                                        <label class="custom-control-label" for="compra">Producto para Compra</label>
                                                    </div>
                                                </div>
                                            </div> 
                                            <div class="form-group">
                                                <label class="col-form-label" for="proveedor"><i class="fa fa-list"></i> Proveedor</label>
                                                <div class="input-group">
                                                    <select class="form-control" id="proveedor" name="proveedor">
                                                        <option value=""> -- </option>
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["proveedor"]) && count($_SESSION["lista"]["proveedor"]) > 0){
                                                                foreach($_SESSION["lista"]["proveedor"] AS $key => $value){
                                                                    $selected = (isset($data["proveedor"]) && $data["proveedor"] == $key) ? "selected" : "";
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["proveedor"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de proveedores es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["proveedor"]) && !$_SESSION["lista"]["proveedor"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de proveedores es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de proveedores es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-proveedor-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div class="form-group d-flex justify-content-between"> 
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="1"  onclick="stepper1.previous()">Anterior</button>
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="3"  onclick="stepper1.next()">Siguiente</button>
                                            </div>
                                        </div> 
                                        
                                        <div id="producto-registro-p-3" class="content <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 3) ? "dstepper-block active" : ""; ?>" role="tabpanel" aria-labelledby="producto-registro-p-3-trigger"> 
                                            
                                            <div class="form-group d-flex justify-content-between"> 
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="1"  onclick="stepper1.previous()">Anterior</button>
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="3"  onclick="stepper1.next()">Siguiente</button>
                                            </div>
                                        </div>

                                        <div id="producto-registro-p-4" class="content <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 4) ? "dstepper-block active" : ""; ?>" role="tabpanel" aria-labelledby="producto-registro-p-4-trigger"> 
                                            <div class="form-group">
                                                <label class="col-form-label" for="compañia"><i class="fa fa-list"></i> Compañía</label>
                                                <select class="form-control" id="compañia" name="compañia">
                                                    <?php
                                                        if(is_array($_SESSION["lista"]["compañia"]) && count($_SESSION["lista"]["compañia"]) > 0){
                                                            foreach($_SESSION["lista"]["compañia"] AS $key => $value){
                                                                $selected = ((isset($data["compañia"]) && $data["compañia"] == $key) || $_SESSION["usuario"]->getCompañia() == $key) ? "selected" : "";
                                                                echo '<option value="'.$key.'" '.$selected.'>'.$value["nombre"].'</option>';
                                                            }
                                                        }else{
                                                            switch(true){
                                                                case ($_SESSION["lista"]["compañia"] < 1):
                                                                    Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de compañías es menor a 1.");
                                                                break;
                                                                case(is_bool($_SESSION["lista"]["compañia"]) && !$_SESSION["lista"]["compañia"]):
                                                                    Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de compañías es FALSE.");
                                                                break;
                                                                default:
                                                                    Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de compañías es desconocido.");
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="sucursal"><i class="fa fa-list"></i> Sucursal</label>
                                                <div class="input-group">
                                                    <select class="form-control" id="sucursal" name="sucursal">
                                                        <option value=""> -- </option>
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["sucursal"]) && count($_SESSION["lista"]["sucursal"]) > 0){
                                                                foreach($_SESSION["lista"]["sucursal"] AS $key => $value){
                                                                    $selected = ((isset($data["sucursal"]) && $data["sucursal"] == $key) || $_SESSION["usuario"]->getSucursal() == $key) ? "selected" : "";
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value["nombre"].'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["sucursal"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de sucursales es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["sucursal"]) && !$_SESSION["lista"]["sucursal"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de sucursales es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de sucursales es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-proveedor-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div class="form-group d-flex justify-content-between"> 
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="2"  onclick="stepper1.previous()">Anterior</button>
                                                <button class="btn btn-outline-success" id="producto-form-registro" value="1" onclick="productoRegistro()">Registrar producto</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <script> 
                                $("#producto-registro-formulario-form input").on('focusout', (e) => {
                                    tareaAgregarData('Registro de producto [<?php echo $data["codigo"] ?>]', e.currentTarget.id, e.currentTarget.value, '#producto-registro-debug');
                                });
                                $("#producto-registro-formulario-form select").on('change', (e) => {
                                    tareaAgregarData('Registro de producto [<?php echo $data["codigo"] ?>]', e.currentTarget.id, e.currentTarget.value, '#producto-registro-debug');
                                });
                                tail.select('#categoria', {
                                    search: true,
                                    classNames: ["flex-grow-1"]
                                });
                                tail.select('#tipo', {
                                    search: true,
                                    classNames: ["flex-grow-1"]
                                });
                                tippy('#producto-tipo-get-form', {
                                    content: 'Agregar un nuevo tipo de producto a la lista.',
                                    delay: [0,500],
                                    animation: 'fade'
                                });
                                tippy('#producto-proveedor-get-form', {
                                    content: 'Agregar un nuevo proveedor de producto a la lista.',
                                    delay: [0,500],
                                    animation: 'fade'
                                });
                                tippy('#producto-categoria-get-form', {
                                    content: 'Agregar una nueva categoría de producto a la lista.',
                                    delay: [0,500],
                                    animation: 'fade'
                                });
                                tippy('#producto-subcategoria-get-form', {
                                    content: 'Agregar una nueva subcategoría de producto a la lista.',
                                    delay: [0,500],
                                    animation: 'fade'
                                });
                                var stepper1Node = document.querySelector('#producto-registro-stepper-1')
                                var stepper1 = new Stepper(document.querySelector('#producto-registro-stepper-1'),{
                                    linear: false,
                                    animation: true
                                });
                            </script>
                        </div>
                        <?php
                    }else{
                        $mensaje['tipo'] = 'warning';
                        $mensaje['cuerpo'] = 'No se pudo comprobar la existencia del producto. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                        Sistema::debug('error', 'producto.class.php - editarFormulario - Error en cpodigo de producto.');
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('error', 'producto.class.php - editarFormulario - Error en id de producto.');
                }
            }else{
                Sistema::debug('error', 'producto.class.php - editarFormulario - Usuario no logueado.');
            }
        }

        public static function editarContenidoFormulario($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) == 4){
                    ?>
                    <div id="producto-<?php echo $data["producto"] ?>-editar-<?php echo $data["tipo"] ?>-process" style="display: none"></div>
                    <form id="producto-<?php echo $data["producto"] ?>-editar-<?php echo $data["tipo"] ?>-form" action="./engine/producto/editar-contenido.php" form="#producto-<?php echo $data["producto"] ?>-editar-<?php echo $data["tipo"] ?>-form" process="#producto-<?php echo $data["producto"] ?>-editar-<?php echo $data["tipo"] ?>-process"> 
                        <div class="form-group mb-0"> 
                            <div class="input-group">
                                <input class="form-control form-control-sm" type="text" id="valor" name="valor" value="<?php echo (strlen($data["value"]) > 0) ? $data["value"] : "" ?>">
                                <input class="form-control form-control-sm d-none" type="text" id="tipo" name="tipo" value="<?php echo $data["tipo"] ?>" readonly>
                                <input class="form-control form-control-sm d-none" type="text" id="productoTipo" name="productoTipo" value="<?php echo $data["productoTipo"] ?>" readonly>
                                <input class="form-control form-control-sm d-none" type="text" id="idProducto" name="idProducto" value="<?php echo $data["producto"] ?>" readonly>
                                <div class="input-group-append">
                                    <button type="button" onclick="productoEditarContenidoFormularioRegistro(<?php echo $data['producto'] ?>,'<?php echo $data['tipo'] ?>')" class="btn btn-sm btn-outline-success"><i class="fa fa-check"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <script>
                        $(document).ready(() => {
                            $("#producto-<?php echo $data["producto"] ?>-editar-<?php echo $data["tipo"] ?>-form #valor").focus();
                            $("#producto-<?php echo $data["producto"] ?>-editar-<?php echo $data["tipo"] ?>-form #valor").keypress((e) => {
                                var keycode = (e.keyCode ? e.keyCode : e.which);
                                if(keycode == '13'){
                                    productoEditarContenidoFormularioRegistro(<?php echo $data['producto'] ?>,'<?php echo $data['tipo'] ?>') 
                                }
                            });
                        })
                    </script>
                    <?php
                }else{
                    Sistema::debug('error', 'producto.class.php - editarContenidoFormulario - Error en arreglo de datos.');
                }
            }else{
                Sistema::debug('error', 'producto.class.php - editarContenidoFormulario - Usuario no logueado.');
            }
        }

        public static function registroFormulario($data, $codificado = true){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) == 3){
                    foreach($data AS $key => $value){
                        if($codificado){
                            if($key != 'tarea' && (!isset($data[$key]) || is_null($data[$key]))){
                                Sistema::debug('alert', 'producto.class.php - registroFormulario - El parámetro '.$key.' tiene un valor incorrecto o inexistente.');
                                $mensaje['tipo'] = 'warning';
                                $mensaje['cuerpo'] = 'Hubo un error con uno de los datos recibidos ['.$key.']. <b>Intente nuevamente o contacte al administrador</b>.';
                                Alert::mensaje($mensaje);
                                exit;
                            }
                        }
                    }
                    if(isset($data["corroborar"]) && $data["corroborar"] === "true"){
                        Producto::corroboraExistenciaFormulario();
                    }else{
                        Session::iniciar();
                        if(false){
                            if(isset($data["tarea"]) && (is_null($data["tarea"]) || strlen($data["tarea"]) == 0)){
                                $_SESSION["usuario"]->tarea("Registro de producto ".(($codificado) ? "codificado" : "no codificado")." [".$data["codigo"]."]", ["codigo" => $data["codigo"], "accion" => "productoRegistroFormulario(false, ".$data["codigo"].", 'Registro de producto ".(($codificado) ? "codificado" : "no codificado")." [".$data["codigo"]."]')"]);
                                echo '<script>loadUsuarioTareasPendientes();</script>';
                            }else{
                                $data = $_SESSION["tarea"][$data["tarea"]]["data"];
                            }
                        }
                        ?>
                        <div class="mine-container">
                            <div class="titulo">Registro de producto <?php echo ($codificado) ? "codificado" : "no codificado" ?></div>
                            <div id="producto-registro-debug"></div>
                            <div id="producto-registro-stepper-1" class="bs-stepper">
                                <div class="bs-stepper-header" role="tablist">
                                    <div class="step <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 1) ? "active" : ""; ?>" data-target="#producto-registro-p-1">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-1" id="producto-registro-p-1-trigger" aria-selected="<?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 1) ? "true" : "false"; ?>">
                                            <span class="bs-stepper-circle">1</span>
                                            <span class="bs-stepper-label">Datos de producto</span>
                                        </button>
                                    </div>
                                    <div class="line"></div>
                                    <div class="step <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 2) ? "active" : ""; ?>" data-target="#producto-registro-p-2">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-2" id="producto-registro-p-2-trigger" aria-selected="<?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 2) ? "true" : "false"; ?>">
                                            <span class="bs-stepper-circle">2</span>
                                            <span class="bs-stepper-label">Stock y Precios</span>
                                        </button>
                                    </div>
                                    <div class="line"></div>
                                    <div class="step <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 3) ? "active" : ""; ?>" data-target="#producto-registro-p-3">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-3" id="producto-registro-p-3-trigger" aria-selected="<?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 3) ? "true" : "false"; ?>">
                                            <span class="bs-stepper-circle">3</span>
                                            <span class="bs-stepper-label">Completar registro</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="bs-stepper-content"> 
                                    <div id="producto-registro-formulario-process" style="display: none;"></div>
                                    <form id="producto-registro-formulario-form" onsubmit="return false" action="./engine/producto/registro.php" form="#producto-registro-formulario-form" process="#producto-registro-formulario-process"> 
                                        <div id="producto-registro-p-1" class="content <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 1) ? "dstepper-block active" : ""; ?>" role="tabpanel" aria-labelledby="producto-registro-p-1-trigger">
                                            <div class="form-group">
                                                <label class="col-form-label <?php echo ($codificado) ? "required" : "" ?>" for="codigo"><i class="fa fa-barcode"></i> Código</label>
                                                <input type="text" class="form-control" required placeholder="<?php echo ($codificado) ? "Código comercial del producto" : "Atajo para venta" ?>" id="codigo" name="codigo" value="<?php echo (isset($data["codigo"])) ? $data["codigo"] : ''; ?>">
                                                <small class="text-muted">
                                                    <?php
                                                        if($codificado){
                                                            echo "El código comercial debe ser exácto al leido en el <b>código de barra del producto</b>.";
                                                        }else{
                                                            echo "El atajo debe ser numérico o dejarse vacío. Si se ingresa 1, el sistema creará el atajo <b>CTRL + 1</b>. <br>Si se deja vacío el sistema no creará atajos y solo podrá buscarse este producto mediante su descripción. <u><b>Los atajos no pueden repetirse</b></u>.";
                                                        }
                                                    ?>
                                                </small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label required" for="tipo"><i class="fa fa-list"></i> Tipo</label>
                                                <div class="input-group">
                                                    <select class="form-control" required id="tipo" name="tipo" <?php echo (!$codificado) ? "readonly" : "" ?>>
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["producto"]["tipo"]) && count($_SESSION["lista"]["producto"]["tipo"]) > 0){
                                                                foreach($_SESSION["lista"]["producto"]["tipo"] AS $key => $value){
                                                                    
                                                                    if(!$codificado){
                                                                        $selected = ($key == 5) ? "selected" : "disabled"; //registr propio compañia
                                                                    }else{
                                                                        $selected = (isset($data["tipo"]) && $data["tipo"] == $key) ? "selected" : "";
                                                                    }
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["producto"]["tipo"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de tipos de producto es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["producto"]["tipo"]) && !$_SESSION["lista"]["producto"]["tipo"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de tipos de producto es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de tipos de producto es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-tipo-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div> 
                                            <div class="form-group">
                                                <label class="col-form-label required" for="categoria"><i class="fa fa-list"></i> Categoría</label>
                                                <div class="input-group">
                                                    <select class="form-control" required id="categoria" name="categoria">
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["producto"]["categoria"]) && count($_SESSION["lista"]["producto"]["categoria"]) > 0){
                                                                foreach($_SESSION["lista"]["producto"]["categoria"] AS $key => $value){
                                                                    $selected = (isset($data["categoria"]) && $data["categoria"] == $key) ? "selected" : "";
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["producto"]["categoria"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de categorías de producto es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["producto"]["categoria"]) && !$_SESSION["lista"]["producto"]["categoria"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de categorías de producto es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de categorías de producto es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-categoria-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label required" for="nombre"><i class="fa fa-pencil-square-o"></i> Descripción</label>
                                                <input type="text" class="form-control" required placeholder="<?php echo ($codificado) ? "Ej.: 1884 CABERNET 750CC" : "COCA COLA CAJÓN X 8" ?>" id="nombre" name="nombre" value="<?php echo (isset($data["nombre"])) ? $data["nombre"] : ""; ?>">
                                                <small class="text-muted">
                                                    <?php
                                                        if($codificado){
                                                            echo "Ingrese la descripción del producto en el siguiente orden: MARCA > TIPO > PRESENTACIÓN. Por ejemplo: <b>1884 VINO MALBEC 750CC</b>.";
                                                        }else{
                                                            echo "Ingrese la descripción del producto en el siguiente orden: MARCA > TIPO > PRESENTACIÓN. Por ejemplo: <b>COCA COLA ZERO CAJON X 12</b>.";
                                                        }
                                                    ?>
                                                </small>
                                            </div>
                                            <div class="form-group d-flex justify-content-end">
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="2" onclick="stepper1.next()">Siguiente</button>
                                            </div>
                                        </div>
                                        
                                        <div id="producto-registro-p-2" class="content <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 2) ? "dstepper-block active" : ""; ?>" role="tabpanel" aria-labelledby="producto-registro-p-2-trigger"> 
                                            <div class="form-group">
                                                <label class="col-form-label" for="subcategoria"><i class="fa fa-list"></i> Sub-Categoría</label>
                                                <div class="input-group">
                                                    <select class="form-control" id="subcategoria" name="subcategoria">
                                                        <option value=""> -- </option>
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["producto"]["subcategoria"]) && count($_SESSION["lista"]["producto"]["subcategoria"]) > 0){
                                                                foreach($_SESSION["lista"]["producto"]["subcategoria"] AS $key => $value){
                                                                    $selected = (isset($data["subcategoria"]) && $data["subcategoria"] == $key) ? "selected" : "";
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["producto"]["fabricante"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de subcagetorias de producto es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["producto"]["fabricante"]) && !$_SESSION["lista"]["producto"]["fabricante"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de subcagetorias de producto es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de subcagetorias de producto es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-subcategoria-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <div class="form-group">
                                                    <label class="col-form-label" for="stock">Stock</label>
                                                    <input type="number" min="0" class="form-control" placeholder="0" id="stock" name="stock">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label" for="minimo">Stock Mímino</label>
                                                    <input type="number" min="0" class="form-control" placeholder="0" id="minimo" name="minimo">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label" for="maximo">Stock Máximo</label>
                                                    <input type="number" min="0" class="form-control" placeholder="0" id="maximo" name="maximo">
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between"> 
                                                <div class="form-group">
                                                    <label class="col-form-label" for="precio">Precio Minorista</label>
                                                    <input type="number" min="0" class="form-control" placeholder="0" id="precio" name="precio">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label" for="precioMayorista">Precio Mayorista</label>
                                                    <input type="number" min="0" class="form-control" placeholder="0" id="precioMayorista" name="precioMayorista">
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-form-label" for="precioKiosco">Precio Kiosco</label>
                                                    <input type="number" min="0" class="form-control" placeholder="0" id="precioKiosco" name="precioKiosco">
                                                </div>
                                            </div>
                                            <div class="form-group d-flex justify-content-between"> 
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="1" onclick="stepper1.previous()">Anterior</button>
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="3" onclick="stepper1.next()">Siguiente</button>
                                            </div>
                                        </div>
                                        <div id="producto-registro-p-3" class="content <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 3) ? "dstepper-block active" : ""; ?>" role="tabpanel" aria-labelledby="producto-registro-p-3-trigger"> 
                                            <div class="form-group">
                                                <label class="col-form-label" for="compañia"><i class="fa fa-list"></i> Compañía</label>
                                                <select class="form-control" id="compañia" name="compañia">
                                                    <?php
                                                        if(is_array($_SESSION["lista"]["compañia"]) && count($_SESSION["lista"]["compañia"]) > 0){
                                                            foreach($_SESSION["lista"]["compañia"] AS $key => $value){
                                                                $selected = ((isset($data["compañia"]) && $data["compañia"] == $key) || $_SESSION["usuario"]->getCompañia() == $key) ? "selected" : "";
                                                                echo '<option value="'.$key.'" '.$selected.'>'.$value["nombre"].'</option>';
                                                            }
                                                        }else{
                                                            switch(true){
                                                                case ($_SESSION["lista"]["compañia"] < 1):
                                                                    Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de compañías es menor a 1.");
                                                                break;
                                                                case(is_bool($_SESSION["lista"]["compañia"]) && !$_SESSION["lista"]["compañia"]):
                                                                    Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de compañías es FALSE.");
                                                                break;
                                                                default:
                                                                    Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de compañías es desconocido.");
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="sucursal"><i class="fa fa-list"></i> Sucursal</label>
                                                <div class="input-group">
                                                    <select class="form-control" id="sucursal" name="sucursal">
                                                        <?php
                                                            if(is_array($_SESSION["lista"]["sucursal"]) && count($_SESSION["lista"]["sucursal"]) > 0){
                                                                foreach($_SESSION["lista"]["sucursal"] AS $key => $value){
                                                                    $selected = ((isset($data["sucursal"]) && $data["sucursal"] == $key) || $_SESSION["usuario"]->getSucursal() == $key) ? "selected" : "";
                                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value["nombre"].'</option>';
                                                                }
                                                            }else{
                                                                switch(true){
                                                                    case ($_SESSION["lista"]["sucursal"] < 1):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de sucursales es menor a 1.");
                                                                    break;
                                                                    case(is_bool($_SESSION["lista"]["sucursal"]) && !$_SESSION["lista"]["sucursal"]):
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de sucursales es FALSE.");
                                                                    break;
                                                                    default:
                                                                        Sistema::debug("Error", "producto.class.php - registroFormulario - El valor de la lista de sucursales es desconocido.");
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" id="producto-proveedor-get-form" class="btn btn-outline-success"><i class="fa fa-plus"></i></button>
                                                    </div> 
                                                </div>
                                            </div>
                                            <div class="form-group d-flex justify-content-between"> 
                                                <button class="btn btn-outline-primary" id="producto-form-step" value="2"  onclick="stepper1.previous()">Anterior</button>
                                                <button class="btn btn-outline-success" id="producto-form-registro" value="1" onclick="productoRegistro('<?php echo ($codificado) ? 'true' : 'false'; ?>')">Registrar producto</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <script> 
                                $(document).ready(() => {
                                    let codificado = <?php echo ($codificado) ? "true" : "false"; ?>;
                                    if(false){
                                        $("#producto-registro-formulario-form input").on('focusout', (e) => {
                                            tareaAgregarData('Registro de producto <?php echo ($codificado) ? "codificado" : "no codificado" ?> [<?php echo $data["codigo"] ?>]', e.currentTarget.id, e.currentTarget.value, '#producto-registro-debug');
                                        });
                                        $("#producto-registro-formulario-form select").on('change', (e) => {
                                            tareaAgregarData('Registro de producto <?php echo ($codificado) ? "codificado" : "no codificado" ?> [<?php echo $data["codigo"] ?>]', e.currentTarget.id, e.currentTarget.value, '#producto-registro-debug');
                                        });
                                    }
                                });
                                tail.select('#categoria', {
                                    search: true,
                                    classNames: ["flex-grow-1"]
                                });
                                <?php
                                    if($codificado){
                                        ?>
                                        tail.select('#tipo', {
                                            search: true,
                                            classNames: ["flex-grow-1"]
                                        });
                                        <?php
                                    }
                                ?>
                                tippy('#producto-tipo-get-form', {
                                    content: 'Agregar un nuevo tipo de producto a la lista.',
                                    delay: [0,500],
                                    animation: 'fade'
                                });
                                tippy('#producto-proveedor-get-form', {
                                    content: 'Agregar un nuevo proveedor de producto a la lista.',
                                    delay: [0,500],
                                    animation: 'fade'
                                });
                                tippy('#producto-categoria-get-form', {
                                    content: 'Agregar una nueva categoría de producto a la lista.',
                                    delay: [0,500],
                                    animation: 'fade'
                                });
                                tippy('#producto-subcategoria-get-form', {
                                    content: 'Agregar una nueva subcategoría de producto a la lista.',
                                    delay: [0,500],
                                    animation: 'fade'
                                });
                                var stepper1Node = document.querySelector('#producto-registro-stepper-1')
                                var stepper1 = new Stepper(document.querySelector('#producto-registro-stepper-1'),{
                                    linear: false,
                                    animation: true
                                });
                            </script>
                        </div>
                        <?php
                    }
                }else{
                    Sistema::debug("error", "producto.class.php - registroFormulario - Datos recibidos de formuarlio incorrecto.");
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. No se cargará el formuario. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    exit;
                }
            }else{
                Sistema::debug("error", "producto.class.php - registroFormulario - Usuario no logueado.");
            }
        }

        public static function noCodifRegistroFormulario(){
            $data = [
                "corroborar" => false,
                "codigo" => null,
                "tarea" => null
            ];
            Producto::registroFormulario($data, false);
        }
    }
?>