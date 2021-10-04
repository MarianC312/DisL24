<?php
    class Cliente{ 
        public static function data($data, $columnas = null){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    if(isset($data["filtroOpcion"]) && is_numeric($data["filtroOpcion"]) && ($data["filtroOpcion"] >= 1 && $data["filtroOpcion"] <= 3)){
                        $query = false; 
                        Session::iniciar();
                        if((is_array($columnas) && count($columnas) > 0)){
                            $cols = "";
                            foreach($columnas AS $key => $value){
                                if($key > 0) $cols .= ",";
                                $cols .= $value;
                            }
                        }else{
                            $cols = "*";
                        }
                        switch($data["filtroOpcion"]){
                            case 1:
                                $nombre = preg_replace( '/[\W]/', '', $data["nombre"]);
                                $query = DataBase::select("cliente", $cols, "nombre LIKE '%".$nombre."%' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'", "");
                            break;
                            case 2:
                                if(isset($data["documento"]) && is_numeric($data["documento"]) && $data["documento"] > 0){ 
                                    $query = DataBase::select("cliente", $cols, "documento = '".$data["documento"]."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'", "");
                                }else{
                                    Sistema::debug('error', 'cliente.class.php - corroboraExistencia - El n° de documento tiene un formato incorrecto. Ref.: '.$data["documento"]);
                                }
                            break;
                            case 3:
                                if(isset($data["id"]) && is_numeric($data["id"]) && $data["id"] > 0){ 
                                    $query = DataBase::select("cliente", $cols, "id = '".$data["id"]."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'", "");
                                }else{
                                    Sistema::debug('error', 'cliente.class.php - corroboraExistencia - El identificador del cliente tiene un formato incorrecto. Ref.: '.$data["id"]);
                                }
                            break;
                        }
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
                            Sistema::debug("error", "cliente.class.php - corroboraExistencia - Error al comprobar la información del cliente. Ref.: ".DataBase::getError());
                        }
                    }else{
                        Sistema::debug('error', 'cliente.class.php - corroboraExistencia - No se recibió una opción de filtro válida. Ref.: '.$data["filtroOpcion"]);
                    }
                }else{
                    Sistema::debug('error', 'cliente.class.php - data - Error en el arreglo de datos recibido.');
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - data - Usuario no logueado.');
            }
            return false;
        }

        public static function buscar($formData){
            if(Sistema::usuarioLogueado()){
                if(isset($formData) && is_array($formData) && count($formData) > 0){
                    $clienteExiste = Cliente::corroboraExistencia($formData);
                    if(is_bool($clienteExiste) && $clienteExiste){
                        $data = Cliente::data($formData, ["id","nombre","documento"]);
                        if(is_array($data)){
                            if(count($data) > 0){
                                switch($formData["filtroOpcion"]){
                                    default:
                                        ?>
                                        <table id="tabla-clientes" class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <td>Documento</td>
                                                    <td>Nombre y Apellido</td>
                                                    <td>Acciones</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    foreach($data AS $key => $value){
                                                        ?>
                                                        <tr> 
                                                            <td><?php echo $value["documento"] ?></td>
                                                            <td><?php echo $value["nombre"] ?></td>
                                                            <td><button type="button" onclick="gotoClienteLegajo(<?php echo $value['id'] ?>)" class="btn btn-primary">Ir a legajo</button></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                        <script> 
                                            $('#tabla-clientestabla-producto-inventario').DataTable({
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
                                        </script>
                                        <?php
                                    break;
                                }
                            }else{
                                $mensaje['tipo'] = 'info';
                                $mensaje['cuerpo'] = 'No se encontraron clientes con los datos brindados.';
                                $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$formData['form'].'\').show(350);$(\''.$formData['process'].'\').hide(350);" class="btn btn-info">Volver a buscar</button></div>';
                                Alert::mensaje($mensaje);
                                Sistema::debug('info', 'cliente.class.php - buscar - No se encontraron registro de clientes. Ref.: '.count($data));
                            }
                        }else{
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al buscar la información del cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$formData['form'].'\').show(350);$(\''.$formData['process'].'\').hide(350);" class="btn btn-danger">Volver a buscar</button></div>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'No se encontraron coincidencias con los datos ingresados. Intente con otra información...';
                        $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around p-2"><button onclick="clienteRegistroFormulario()" class="btn btn-info">Registrar cliente</button><button onclick="$(\''.$formData['form'].'\').show(350);$(\''.$formData['process'].'\').hide(350);" class="btn btn-outline-info">Reintentar</button></div>';
                        Alert::mensaje($mensaje);
                        Sistema::debug('info', 'cliente.class.php - buscar - Cliente inexistente.');
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('error', 'cliente.class.php - buscar - Error en el arreglo de datos del formulario.');
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - buscar - Usuario no logueado.');
            }
        }

        public static function registroFormulario(){ 
            if(Sistema::usuarioLogueado()){
                ?>
                <div class="mine-container">
                    <div class="titulo">Registro de cliente</div> 
                    <div id="cliente-registrar-process"></div>
                    <form id="cliente-registrar-form" action="./engine/cliente/registro.php" form="#cliente-registrar-form" process="#cliente-registrar-process">
                        <div class="form-group">
                            <label class="col-form-label required" for="nombre"><i class="fa fa-user-circle"></i> Nombre y Apellido</label>
                            <input type="nombre" class="form-control" required id="nombre" name="nombre" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label required" for="documento"><i class="fa fa-address-card"></i> N° de Documento</label>
                            <input type="documento" class="form-control" required id="documento" name="documento" autocomplete="off"> 
                            <small class="text-muted">Ingrese el número sin puntos.</small>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="telefono"><i class="fa fa-phone-square"></i> Teléfono</label>
                            <input type="telefono" class="form-control" id="telefono" name="telefono" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="domicilio"><i class="fa fa-home"></i> Domicilio</label>
                            <input type="domicilio" class="form-control" id="domilicio" name="domicilio" autocomplete="off"> 
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="email"><i class="fa fa-envelope-square"></i> Email</label>
                            <input type="email" class="form-control" id="email" name="email" autocomplete="off"> 
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-success" onclick="clienteRegistro()">Registrar</button>
                        </div>
                    </form>
                    <script>
                        $("#cliente-registrar-form input").on("keypress", (e) => {
                            var keycode = (e.keyCode ? e.keyCode : e.which);
                            if(keycode == '13'){
                                clienteRegistro();
                            }
                        })
                    </script>
                </div>
                <?php     
            }else{
                Sistema::debug('error', 'cliente.class.php - registroFormulario - Usuario no logueado.');
            } 
        }

        public static function buscarFormulario(){
            if(Sistema::usuarioLogueado()){
                ?> 
                <div class="mine-container"> 
                    <div class="titulo">Buscar un cliente</div>
                    <div id="cliente-buscar-process" style="display: none"></div>
                    <form id="cliente-buscar-form" action="./engine/cliente/buscar.php" form="#cliente-buscar-form" process="#cliente-buscar-process"> 
                        <fieldset class="form-group">
                            <div class="d-flex justify-content-around">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" onchange="clienteBuscarFormularioUpdateBusqueda()" name="filtroOpcion" id="filtroOpcion1" value="1" checked="">
                                        Por nombre <i class="fa fa-pencil-square-o"></i>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" onchange="clienteBuscarFormularioUpdateBusqueda()" name="filtroOpcion" id="filtroOpcion2" value="2">
                                        Por documento <i class="fa fa-address-card"></i>
                                    </label>
                                </div>
                            </div>
                        </fieldset>
                        <div id="container-nombre" class="form-group" style="display: none">
                            <label class="col-form-label required" for="nombre"><i class="fa fa-user"></i> Nombre</label>
                            <input type="documento" class="form-control" required id="nombre" name="nombre" autocomplete="off">
                        </div>
                        <div id="container-documento" class="form-group" style="display: none">
                            <label class="col-form-label required" for="documento"><i class="fa fa-address-card"></i> N° de Documento</label>
                            <input type="documento" class="form-control" required id="documento" name="documento" autocomplete="off"> 
                            <small class="text-muted">Ingrese el n° sin puntos.</small>
                        </div>
                        <div class="form-group">
                            <button type="button" onclick="clienteBuscar()" class="btn btn-success btn-iconed">Buscar <i class="fa fa-search"></i></button>
                        </div>
                    </form>
                    <script>
                        $("#cliente-buscar-form input").on("keypress", (e) => {
                            let keycode = (e.keyCode ? e.keyCode : e.which);
                            if(keycode == '13'){
                                clienteBuscar()
                            }
                        })
                        clienteBuscarFormularioUpdateBusqueda();
                    </script>
                </div> 
                <?php
            }else{
                Sistema::debug('error', 'cliente.class.php - buscarFormulario - Usuario no logueado.');
            }
        }

        public static function compraData($idCliente, $sucursal = null, $compañia = null){
            if(Sistema::usuarioLogueado()){
                if(isset($idCliente) && is_numeric($idCliente) && $idCliente > 0){
                    Session::iniciar();
                    $query = DataBase::select("compañia_sucursal_venta", "*", "cliente = '".$idCliente."' AND compañia = '".((is_numeric($compañia)) ? $compañia : $_SESSION["usuario"]->getCompañia())."'", "ORDER BY CASE WHEN pago = 8 AND estado = 1 THEN 0 ELSE 1 END, fechaCarga DESC");
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
                        Sistema::debug('error', 'cliente.class.php - compraData - Error al consultar la información de las compras. Ref.: '.DataBase::getError());
                    }
                }else{
                    Sistema::debug('error', 'cliente.class.php - compraData - Error en identificador de cliente. Ref.: '.$idCliente);
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - compraData - Usuario no logueado.');
            }
            return false;
        }

        public static function compraLista($idCliente, $small = false){
            if(Sistema::usuarioLogueado()){
                if(isset($idCliente) && is_numeric($idCliente) && $idCliente > 0){
                    $data = Cliente::compraData($idCliente);
                    if(is_array($data)){
                        ?>
                        <div class="mine-container <?php echo ($small === "true") ? "sm" : "" ?>">
                            <div class="titulo">Historial de compras</div>
                            <div class="p-1">
                                <table id="cliente-historial-compra" class="table table-hover w-100">
                                    <thead>
                                        <tr>
                                            <td>Estado</td>
                                            <td>Monto</td>
                                            <td>Fecha</td>
                                            <td class="text-right">Acciones</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            if(count($data) > 0){
                                                foreach($data AS $key => $value){
                                                    if($value["estado"] == 0){
                                                        $icono = "times";
                                                        $class = "danger";
                                                        $alt = "Compra anulada.";
                                                    }else{
                                                        switch($value["pago"]){
                                                            case 8:
                                                                $icono = "info";
                                                                $class = "info";
                                                                $alt = "Compra adeudada.";
                                                                break;
                                                            default:
                                                                $icono = "check";
                                                                $class = "success";
                                                                $alt = "Compra realizada.";
                                                                break;
                                                        }
                                                    } 
                                                    ?>
                                                    <tr>
                                                        <td><i class="fa fa-<?php echo $icono ?>-circle text-<?php echo $class ?>" title="<?php echo $alt ?>"></i></td>
                                                        <td>$ <?php echo number_format($value["total"], 2, ",", ".") ?></td>
                                                        <td><?php echo date("d/m/Y, H:i A", strtotime($value["fechaCarga"])); ?></td>
                                                        <td class="text-right">
                                                            <div class="btn-group">
                                                                <?php
                                                                    if(Caja::corroboraAcceso()){
                                                                        $idCaja = $_SESSION["usuario"]->getActividadCaja();
                                                                        $actividad = 1;
                                                                        if($value["pago"] == 8 && $value["estado"] == 1 && is_numeric($idCaja) && $idCaja > 0 && Compania::cajaCorroboraExistencia($idCaja)){
                                                                            echo '<button type="button" id="pagar" onclick="cajaPagoFormulario('.$idCaja.', null, '.$value["id"].')" class="btn btn-sm btn-info"><i class="fa fa-usd"></i></button>';
                                                                        }
                                                                    } 
                                                                ?>
                                                                <button type="button" id="factura" onclick="facturaVisualizar(<?php echo $value['id'] ?>)" class="btn btn-sm btn-outline-info"><i class="fa fa-file-pdf-o"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            }else{
                                                ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No se encontraron registros de compra.</td>
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
                                    dataTableSet("#cliente-historial-compra");
                                    tippy('#pagar', {
                                        content: 'Pagar cuenta',
                                        delay: [150,150],
                                        animation: 'fade'
                                    });
                                    tippy('#factura', {
                                        content: 'Visualizar ticket',
                                        delay: [150,150],
                                        animation: 'fade'
                                    });
                                </script>
                            </div>
                        </div>
                        <?php
                    }else{ 
                        $mensaje['tipo'] = 'warning';
                        $mensaje['cuerpo'] = 'Hubo un error con la información recibida. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    Sistema::debug('error', 'cliente.class.php - compraLista - Error en identificador de cliente. Ref.: '.$idCliente);
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos del cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - compraLista - Usuario no logueado.');
            }
        }

        public static function legajo($idCliente){
            if(Sistema::usuarioLogueado()){
                if(isset($idCliente) && is_numeric($idCliente) && $idCliente > 0){
                    $clienteExiste = Cliente::corroboraExistencia(["filtroOpcion" => 3, "id" => $idCliente]);
                    if($clienteExiste){
                        ?>
                        <div class="mine-container">
                            <button type="button" class="btn btn-primary d-none" onclick="gotoClienteLegajo(<?php echo $idCliente ?>)">Refresh</button>
                            <div class="titulo">Legajo de <b><?php echo mb_strtoupper(Cliente::getNombre($idCliente)) ?></b></div>
                            <div class="row">
                                <div class="col-md-4">
                                    <?php Cliente::editarFormulario($idCliente) ?>
                                </div>
                                <div class="col-md-8">
                                    <div id="cliente-container-compra" class="w-100">
                                        <?php Cliente::compraLista($idCliente, "true") ?>
                                    </div>
                                    <div class="w-100">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                    }else{
                        $mensaje['tipo'] = 'warning';
                        $mensaje['cuerpo'] = 'Hubo un error con los datos del cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                        $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                            <button type="button" onclick="gotoClienteLegajo('.$idCliente.')" class="btn btn-warning btn-iconed">Recargar legajo <i class="fa fa-retweet"></i></button>
                            </div>';
                        Alert::mensaje($mensaje);
                        Sistema::debug('error', 'cliente.class.php - legajo - Error con los datos del cliente. Ref.: '.count($data));
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos del cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                        <button type="button" onclick="gotoClienteLegajo('.$idCliente.')" class="btn btn-danger btn-iconed">Recargar legajo <i class="fa fa-retweet"></i></button>
                        </div>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('error', 'cliente.class.php - legajo - Error en identificador de cliente. Ref.: '.$idCliente);
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - legajo - Usuario no logueado.');
            }
        }

        public static function getNombre($idCliente){
            if(Sistema::usuarioLogueado()){
                if(isset($idCliente) && is_numeric($idCliente) && $idCliente > 0){
                    $data = Cliente::data(["filtroOpcion" => 3, "id" => $idCliente], ["id","nombre"]);
                    if(is_array($data)){
                        if(count($data) == 1){
                            return $data[$idCliente]["nombre"];
                        }else{
                            Sistema::debug('error', 'cliente.class.php - getNombre - No se encontró registro del cliente.');
                        }
                    }else{
                        Sistema::debug('error', 'cliente.class.php - getNombre - Error al recibir la información del cliente.');
                    }
                }else{
                    Sistema::debug('error', 'cliente.class.php - getNombre - El identificador del cliente recibido tiene un formato incorrecto. Ref.: '.$idCliente);
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - getNombre - Usuario no logueado.');
            }
            return false;
        }

        public static function getDomicilio($idCliente){
            if(Sistema::usuarioLogueado()){
                if(isset($idCliente) && is_numeric($idCliente) && $idCliente > 0){
                    $data = Cliente::data(["filtroOpcion" => 3, "id" => $idCliente], ["id","domicilio"]);
                    if(is_array($data)){
                        if(count($data) == 1){
                            return $data[$idCliente]["domicilio"];
                        }else{
                            Sistema::debug('error', 'cliente.class.php - getDomicilio - No se encontró registro del cliente.');
                        }
                    }else{
                        Sistema::debug('error', 'cliente.class.php - getDomicilio - Error al recibir la información del cliente.');
                    }
                }else{
                    Sistema::debug('error', 'cliente.class.php - getDomicilio - El identificador del cliente recibido tiene un formato incorrecto. Ref.: '.$idCliente);
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - getDomicilio - Usuario no logueado.');
            }
            return false;
        }

        public static function getId($documento){
            if(Sistema::usuarioLogueado()){
                if(isset($documento) && is_numeric($documento) && $documento > 0){
                    $data = Cliente::data(["filtroOpcion" => 2, "documento" => $documento], ["id"]);
                    if(is_array($data)){
                        if(count($data) == 1){
                            return $data[0]["id"];
                        }else{
                            Sistema::debug('error', 'cliente.class.php - getId - No se encontró registro del cliente.');
                        }
                    }else{
                        Sistema::debug('error', 'cliente.class.php - getId - Error al recibir la información del cliente.');
                    }
                }else{
                    Sistema::debug('error', 'cliente.class.php - getId - El documento recibido tiene un formato incorrecto. Ref.: '.$documento);
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - getId - Usuario no logueado.');
            }
            return false;
        }

        public static function registro($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    $clienteExiste = Cliente::corroboraExistencia(["documento" => $data["documento"], "filtroOpcion" => 2]);
                    if($clienteExiste){
                        $idCliente = Cliente::getId($data["documento"]);
                        Sistema::debug("info", "cliente.class.php - registro - Cliente ya registrado.");
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'El cliente ya se encuentra registrado.';
                        $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around"> 
                        '.((is_numeric(($idCliente)) ? '<button type="button" onclick="gotoClienteLegajo('.$idCliente.')" class="btn btn-info">Ir a legajo</button>' : '')).'
                        <button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-outline-info">Registrar otro cliente</button>
                            </div>';
                        Alert::mensaje($mensaje);
                        return;
                    }else{
                        $query = DataBase::insert("cliente", "nombre,documento,telefono,domicilio,email,sucursal,compañia", "'".$data["nombre"]."','".$data["documento"]."',".((isset($data["telefono"])) ? "'".$data["telefono"]."'" : "NULL").",".((isset($data["domicilio"])) ? "'".$data["domicilio"]."'" : "NULL").",".((isset($data["email"])) ? "'".$data["email"]."'" : "NULL").",'".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."'");
                        if($query){
                            Session::iniciar();
                            $_SESSION["usuario"]->tareaEliminar('Registro de cliente ['.$data["documento"].']');
                            Sistema::debug("success", "cliente.class.php - registro - cliente registrado satisfactoriamente.");
                            $mensaje['tipo'] = 'success';
                            $mensaje['cuerpo'] = 'Se registró el cliente <b>'.$data["nombre"].'</b> satisfactoriamente.';
                            $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                                <button type="button" onclick="gotoClienteLegajo('.DataBase::getLastId().')" class="btn btn-success">Ir a legajo</button> 
                                <button type="button" onclick="clienteRegistroFormulario()" class="btn btn-outline-success">Registrar otro cliente</button> 
                            </div>';
                            Alert::mensaje($mensaje);
                            return true;
                        }else{
                            Sistema::debug('error', 'cliente.class.php - registro - Error en query de registro de cliente.');
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al registrar el cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block"><button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                            return false;
                        }
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug("error", "cliente.class.php - registro - Arreglo de datos del formulario incorrecto.");
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - registro - Usuario no logueado.');
            } 
        }
        
        public static function corroboraExistencia($data, $cargaFormularioRegistro = false){ 
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    if(isset($data["filtroOpcion"]) && is_numeric($data["filtroOpcion"]) && ($data["filtroOpcion"] >= 1 && $data["filtroOpcion"] <= 3)){
                        $query = false;
                        Session::iniciar();
                        switch($data["filtroOpcion"]){
                            case 1:
                                $nombre = preg_replace( '/[\W]/', '', $data["nombre"]);
                                $query = DataBase::select("cliente", "id", "LOWER(nombre) LIKE LOWER('%".$nombre."%') AND compañia = '".$_SESSION["usuario"]->getCompañia()."'", "");
                            break;
                            case 2:
                                if(isset($data["documento"]) && is_numeric($data["documento"]) && $data["documento"] > 0){ 
                                    $query = DataBase::select("cliente", "id", "documento = '".$data["documento"]."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'", "");
                                }else{
                                    Sistema::debug('error', 'cliente.class.php - corroboraExistencia - El n° de documento tiene un formato incorrecto. Ref.: '.$data["documento"]);
                                }
                            break;
                            case 3:
                                if(isset($data["id"]) && is_numeric($data["id"]) && $data["id"] > 0){ 
                                    $query = DataBase::select("cliente", "id", "id = '".$data["id"]."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'", "");
                                }else{
                                    Sistema::debug('error', 'cliente.class.php - corroboraExistencia - Error en identificador de cliente. Ref.: '.$data["id"]);
                                }
                            break;
                        }
                        if($query){
                            return (DataBase::getNumRows($query) >= 1) ? true : false;
                        }else{
                            Sistema::debug("error", "cliente.class.php - corroboraExistencia - Error al comprobar la información del cliente. Ref.: ".DataBase::getError());
                        }
                    }else{
                        Sistema::debug('error', 'cliente.class.php - corroboraExistencia - No se recibió una opción de filtro válida. Ref.: '.$data["filtroOpcion"]);
                    }
                }else{
                    Sistema::debug("error", "cliente.class.php - corroboraExistencia - El arreglo de datos recibido es incorrecto.");
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - corroboraExistencia - Usuario no logueado.');
            }
            return false;
        }

        public static function editarFormulario($idCliente){ 
            if(Sistema::usuarioLogueado()){
                if(isset($idCliente) && is_numeric($idCliente) && $idCliente > 0){
                    $data = Cliente::data(["id" => $idCliente, "filtroOpcion" => 3]);
                    if(is_array($data) && count($data) == 1){
                        ?>
                        <div class="mine-container sm"> 
                            <div class="titulo">Edición legajo <?php echo mb_strtoupper($data[$idCliente]["nombre"]) ?></div>
                            <div id="cliente-editar-process"></div>
                            <form id="cliente-editar-form" action="./engine/cliente/editar.php" form="#cliente-editar-form" process="#cliente-editar-process">
                                <div class="form-group">
                                    <label class="col-form-label required" for="nombre" ><i class="fa fa-user-circle"></i> Nombre y Apellido</label>
                                    <input type="text" class="form-control" required value="<?php echo $data[$idCliente]["nombre"]?>" id="nombre" name="nombre"> 
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label required" for="documento"><i class="fa fa-address-card"></i> Documento</label>
                                    <input type="text" class="form-control" required value="<?php echo $data[$idCliente]["documento"]?>" id="documento" name="documento">  
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label" for="telefono"><i class="fa fa-phone-square"></i> Teléfono</label>
                                    <input type="text" class="form-control" value="<?php echo $data[$idCliente]["telefono"]?>" id="telefono" name="telefono">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label" for="domicilio"><i class="fa fa-home"></i> Domicilio</label>
                                    <input type="text" class="form-control" value="<?php echo $data[$idCliente]["domicilio"]?>" id="domilicio" name="domicilio"> 
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label" for="email"><i class="fa fa-envelope-square"></i> Email</label>
                                    <input type="text" class="form-control" value="<?php echo $data[$idCliente]["email"]?>" id="email" name="email">
                                </div>
                                <div class="form-group d-none">
                                    <input type="text" class="form-control d-none" value="<?php echo $data[$idCliente]["id"]?>" id="idCliente" name="idCliente" readonly>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-success" onclick="clienteEditar(<?php echo $data[$idCliente]['id'] ?>)">Guardar cambios</button>
                                </div>
                            </form> 
                        </div>
                        <?php 
                    }else{
                        Sistema::debug("error", "cliente.class.php - editaFormulario - Error en consulta información de cliente. Ref.: ".DataBase::getError());
                    }  
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                        Sistema::debug('error', 'cliente.class.php - editarFormulario - Error en documento.');
                    }
                }else{
                Sistema::debug('error', 'cliente.class.php - editarFormulario - Usuario no logueado.'); 
            }
        }

        public static function editar($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    $clienteExiste = Cliente::corroboraExistencia(["documento" => $data["documento"], "filtroOpcion" => 2]);
                    if(!$clienteExiste){
                        $query = DataBase::update("cliente","nombre = '".$data["nombre"]."', documento = '".$data["documento"]."', telefono = ".((isset($data["telefono"]) && !is_null($data["telefono"]) && strlen($data["telefono"]) > 0) ? "'".$data["telefono"]."'" : "NULL").", domicilio = ".((isset($data["domicilio"]) && !is_null($data["domicilio"]) && strlen($data["domicilio"]) > 0) ? "'".$data["domicilio"]."'" : "NULL").", email = ".((isset($data["email"]) && !is_null($data["email"]) && strlen($data["email"]) > 0) ? "'".$data["email"]."'" : "NULL"), "id = '".$data["idCliente"]."' AND compañia = '".$_SESSION["usuario"]->getCompañia()."'");
                        if($query){
                            Session::iniciar();
                            $_SESSION["usuario"]->tareaEliminar('Edición de cliente ['.$data["documento"].']');
                            $mensaje['tipo'] = 'success';
                            $mensaje['cuerpo'] = 'Información del cliente <b>'.$data["nombre"].'</b> actualizada satisfactoriamente.'; 
                            Alert::mensaje($mensaje);
                        }else{
                            Sistema::debug('error', 'cliente.class.php - edicion - Error al actualizar la información del cliente. Ref.: '.DataBase::getError());
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al editar el cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block"><button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al modificar los datos del cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                        Sistema::debug("error", "cliente.class.php - edicion - Error al comprobar la existencia del cliente.");
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug("error", "cliente.class.php - edicion - Arreglo de datos del formulario incorrecto.");
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - edicion - Usuario no logueado.');
            }
        }
    }
?>