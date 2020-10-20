<?php
    class Producto{
        public static function inventarioEditarContenidoFormulario($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) == 3){
                    ?>
                    <div id="producto-<?php echo $data["producto"] ?>-inventario-editar-contenido-process" style="display: none"></div>
                    <form id="producto-<?php echo $data["producto"] ?>-inventario-editar-contenido-form" action="./engine/producto/inventario-editar-contenido.php" form="#producto-<?php echo $data["producto"] ?>-inventario-editar-contenido-form" process="#producto-<?php echo $data["producto"] ?>-inventario-editar-contenido-process"> 
                        <div class="form-group mb-0"> 
                            <div class="input-group">
                                <input class="form-control form-control-sm" type="number" id="cantidad" min="0" value="<?php echo $data["cantidad"] ?>">
                                <input class="form-control form-control-sm d-none" type="text" id="tipo" value="<?php echo $data["tipo"] ?>" readonly>
                                <div class="input-group-append">
                                    <button type="button" onclick="productoInventarioEditarContenido(<?php echo $data['producto'] ?>,'<?php echo $data['tipo'] ?>')" class="btn btn-sm btn-outline-success"><i class="fa fa-check"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
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
                                            foreach($data AS $key => $value){
                                                $productoTipo = $_SESSION["lista"]["producto"]["tipo"];
                                                $productoCategoria = $_SESSION["lista"]["producto"]["categoria"];
                                                $productoSubcategoria = $_SESSION["lista"]["producto"]["subcategoria"];
                                                ?>
                                                <tr id="producto-<?php echo $key ?>" data-key="<?php echo $key ?>">
                                                    <th scope="row"><?php echo $value["codigoBarra"] ?></th>
                                                    <td><?php echo $value["nombre"] ?></td>
                                                    <td id="stock" class="text-center"><?php echo (!isset($data["sucursal"]) || $_SESSION["usuario"]->getSucursal() != $data["sucursal"]) ? "<a href='#/'><i class='fa fa-plus-circle'></i> stock inicial</a>" : ((is_numeric($value["stock"])) ? $value["stock"] : "0") ?></td>
                                                    <td id="minimo" class="text-center"><?php echo (is_numeric($value["minimo"])) ? $value["minimo"] : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td id="maximo" class="text-center"><?php echo (is_numeric($value["maximo"])) ? $value["maximo"] : "<a href='#/'><i class='fa fa-plus-circle'></i></a>" ?></td>
                                                    <td><?php echo $productoTipo[$value["tipo"]]; ?></td>
                                                    <td><?php echo $productoCategoria[$value["categoria"]] ?></td>
                                                    <td><?php echo (is_numeric($value["subcategoria"])) ? $productoSubcategoria[$value["subcategoria"]] : "<span class='text-muted'>No categorizado</span>" ?></td>
                                                    <td></td>
                                                </tr>
                                                <?php
                                            } 
                                        }else{
                                            ?> 
                                            <tr>
                                                <td colspan="10" class="text-center">
                                                    No se encontraron productos registrados en la compañia. Para cargar un nuevo producto clickee en el siguiente <a href="#/" onclick="productoRegistroFormulario()">link</a>.
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
                                    }else{
                                        if(is_bool($data) && !$data){
                                            Sistema::debug('error', 'producto.class.php - inventario - Data boolean FALSE.');
                                        }else{
                                            Sistema::debug('error', 'producto.class.php - inventario - Error desconocido.');
                                        }
                                        ?> 
                                        <tr>
                                            <td colspan="10" class="text-center">
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
                                productoInventarioEditarContenidoFormulario(e.currentTarget.parentNode.parentNode.getAttribute("data-key"),e.currentTarget.parentNode.getAttribute("id"));
                            });
                            tippy('td a', {
                                content: 'Click para agregar un nuevo valor.',
                                delay: [150,150],
                                animation: 'fade'
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

        public static function data($idSucursal = null){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("producto p LEFT JOIN producto_stock ps ON ps.producto = p.id", "p.id AS 'idProducto',p.nombre, p.tipo, p.codigoBarra,p.categoria,p.subcategoria,ps.*", "p.compañia = '".$_SESSION["usuario"]->getCompañia()."' AND '".((is_numeric($idSucursal) && $idSucursal > 0) ? " AND ps.sucursal = ".$idSucursal."" : "1")."' AND estado = 1", "ORDER BY p.nombre ASC");
                if($query){
                    if(DataBase::getNumRows($query) > 0){
                        $data = [];
                        while($dataQuery = DataBase::getArray($query)){
                            $data[$dataQuery["idProducto"]] = $dataQuery;
                        }
                        foreach($data AS $key => $value){
                            foreach($value AS $iKey => $iValue){
                                if(is_int($iKey)){
                                    unset($data[$key][$iKey]);
                                }
                            }
                        }
                        Sistema::debug('success', 'producto.class.php - data - Información encontrada satisfactoriamente. Prod. encontrados:'.DataBase::getNumRows($query));
                        return $data;
                    }else{
                        Sistema::debug('info', 'producto.class.php - data - No se encontraron productos registrados. Inf.:'.DataBase::getNumRows($query));
                        return [];
                    }
                }else{
                    Sistema::debug('error', 'producto.class.php - data - Error en la consulta de base de datos. Inf.:'.DataBase::getError());
                    return false;
                }
            }else{
                Sistema::debug('error', 'producto.class.php - data - Usuario no logueado.');
            }
        }

        public static function corroboraExistencia($data, $cargaFormularioRegistro = false){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && (count($data) == 4 || count($data) == 1)){
                    if(isset($data["codigo"]) && is_numeric($data["codigo"]) && $data["codigo"] > 0){
                        $query = DataBase::select("producto", "id", "codigoBarra = '".$data["codigo"]."'", "");
                        if($query){
                            if(DataBase::getNumRows($query) == 1){
                                $dataQuery = DataBase::getArray($query);
                                if($cargaFormularioRegistro){
                                    Sistema::debug("success", "producto.class.php - corroboraExistencia - Producto encontrado, carga de formulario de edición para producto ID: ".$dataQuery["id"].".");
                                    Producto::editarFormulario($dataQuery["id"]);
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

        public static function corroboraExistenciaFormulario(){
            if(Sistema::usuarioLogueado()){
                ?>
                <div class="mine-container">
                    <div class="titulo">Corroborar existencia</div>
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
                            <button type="button" onclick="productoCorroboraExistencia()" class="btn btn-success">Corroborar</button>
                        </div>
                    </form>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'producto.class.php - corroboraExistenciaFormulario - Usuario no logueado.');
            }
        }

        public static function registro($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    $productoExiste = Producto::corroboraExistencia(["codigo" => $data["codigo"]]);
                    if($productoExiste){
                        Sistema::debug("info", "producto.class.php - registro - El producto ya existe en la base de datos.");
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'El producto ya se encuentra registrado.';
                        $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                            <button type="button" onclick="productoEditarFormulario()" class="btn btn-info">Editar producto</button>
                            <button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-outline-info">Regresar</button>
                        </div>';
                        Alert::mensaje($mensaje);
                        return;
                    }else{
                        $query = DataBase::insert("producto", "nombre,tipo,codigoBarra,categoria,subcategoria,fabricante,inventariable,paraVenta,paraCompra,proveedor,sucursal,compañia", "'".$data["nombre"]."','".$data["tipo"]."','".$data["codigo"]."','".$data["categoria"]."',".((isset($data["subcategoria"]) && is_numeric($data["subcategoria"])) ? $data["subcategoria"] : "NULL").",".((isset($data["fabricante"]) && is_numeric($data["fabricante"])) ? $data["fabricante"] : "NULL").",".((isset($data["inventariable"]) && is_numeric($data["inventariable"])) ? $data["inventariable"] : "NULL").",".((isset($data["venta"]) && is_numeric($data["venta"])) ? $data["venta"] : "NULL").",".((isset($data["compra"]) && is_numeric($data["compra"])) ? $data["compra"] : "NULL").",".((isset($data["proveedor"]) && is_numeric($data["proveedor"])) ? $data["proveedor"] : "NULL").",".((isset($data["sucursal"]) && is_numeric($data["sucursal"])) ? $data["sucursal"] : "NULL").",".((isset($data["compañia"]) && is_numeric($data["compañia"])) ? $data["compañia"] : $_SESSION["usuario"]->getCompañia()));
                        if($query){
                            Session::iniciar();
                            $_SESSION["usuario"]->tareaEliminar('Registro de producto ['.$data["codigo"].']');
                            Sistema::debug("success", "producto.class.php - registro - Producto registrado satisfactoriamente.");
                            $mensaje['tipo'] = 'success';
                            $mensaje['cuerpo'] = 'Se registró el producto <b>'.$data["nombre"].'</b> satisfactoriamente.';
                            $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                                <button type="button" onclick="productoRegistroFormulario()" class="btn btn-success">Registrar otro producto</button>
                                <button type="button" onclick="productoEditarFormulario('.DataBase::getLastId().')" class="btn btn-outline-success">Continuar al producto</button>
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

        public static function editarFormulario($idProducto){
            if(Sistema::usuarioLogueado()){
                if(isset($idProducto) && is_numeric($idProducto) && $idProducto > 0){
                    $data = [];
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

        public static function registroFormulario($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) == 3){
                    foreach($data AS $key => $value){
                        if($key != 'tarea' && (!isset($data[$key]) || is_null($data[$key]))){
                            Sistema::debug('alert', 'producto.class.php - registroFormulario - El parámetro '.$key.' tiene un valor incorrecto o inexistente.');
                            $mensaje['tipo'] = 'warning';
                            $mensaje['cuerpo'] = 'Hubo un error con uno de los datos recibidos ['.$key.']. <b>Intente nuevamente o contacte al administrador</b>.';
                            Alert::mensaje($mensaje);
                            exit;
                        }
                    }
                    if(isset($data["corroborar"]) && $data["corroborar"] === "true"){
                        Producto::corroboraExistenciaFormulario();
                    }else{
                        Session::iniciar();
                        if(isset($data["tarea"]) && (is_null($data["tarea"]) || strlen($data["tarea"]) == 0)){
                            $_SESSION["usuario"]->tarea("Registro de producto [".$data["codigo"]."]", ["codigo" => $data["codigo"], "accion" => "productoRegistroFormulario(false, ".$data["codigo"].", 'Registro de producto [".$data["codigo"]."]')"]);
                            echo '<script>loadUsuarioTareasPendientes();</script>';
                        }else{
                            $data = $_SESSION["tarea"][$data["tarea"]]["data"];
                        }
                        ?>
                        <div class="mine-container">
                            <div class="titulo">Registro de producto</div>
                            <div id="producto-registro-debug"></div>
                            <div id="producto-registro-stepper-1" class="bs-stepper">
                                <div class="bs-stepper-header" role="tablist">
                                    <div class="step <?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 1) ? "active" : ""; ?>" data-target="#producto-registro-p-1">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-1" id="producto-registro-p-1-trigger" aria-selected="<?php echo (isset($data["producto-form-step"]) && $data["producto-form-step"] == 1) ? "true" : "false"; ?>">
                                            <span class="bs-stepper-circle">1</span>
                                            <span class="bs-stepper-label">Datos obligatorios</span>
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
    }
?>