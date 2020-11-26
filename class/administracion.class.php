<?php
    class Administracion {
        public static $facturaEstado = ["Anulada", "Pendiente", "Paga"];
        public static $facturaEstadoClass = ["danger", "info", "success"];

        public static function clienteBuscarFormulario(){ 
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                if($_SESSION["usuario"]->isAdmin()){
                    ?>
                    <div class="mine-container">
                        <?php Compania::buscarFormulario("administracionClienteBuscar()"); ?>
                    </div>
                    <?php    
                }else{
                    Sistema::debug('error', 'administracion.class.php - clienteBuscarFormulario - Acceso denegado.');
                }
            }else{
                Sistema::debug('error', ' administracion.class.php - clienteBuscarFormulario - Usuario no logueado.');
            } 
        } 

        public static function clienteFacturacionData($idCompañia){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                if($_SESSION["usuario"]->isAdmin()){
                    if(isset($idCompañia) && is_numeric($idCompañia) && $idCompañia > 0){
                        if(Compania::corroboraExistencia($idCompañia)){
                            $query = DataBase::select("sistema_compañia_facturacion", "*", "compañia = '".$idCompañia."'", "ORDER BY estado ASC, fechaCarga DESC");
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
                    Sistema::debug('error', 'administracion.class.php - clienteFacturacionData - Acceso denegado.');
                }
            }else{
                Sistema::debug('error', 'administracion.class.php - clienteFacturacionData - Usuario no logueado.');
            }
            return false;
        }

        public static function clienteFacturacionRegistro($data){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                if($_SESSION["usuario"]->isAdmin()){
                    if(isset($data) && is_array($data) && count($data) == 5){
                        if($data["idCompañia"] === $data["idCompañia2"]){
                            $data["response"] = File::upload($data["file"], File::administracionClienteFacturacionPath($data["idCompañia"]));
                            if($data["response"][0]["estado"]){
                                $query = DataBase::insert("sistema_compañia_facturacion", "recibo,total,file,compañia,operador", "'".$data["recibo"]."','".$data["total"]."','".$data["response"][0]["file"]."','".$data["idCompañia"]."','".$_SESSION["usuario"]->getId()."'");
                                if($query){
                                    $mensaje['tipo'] = 'success';
                                    $mensaje['cuerpo'] = 'Se registró la facturació satisfactoriamente.';
                                    Alert::mensaje($mensaje);
                                    echo '<script>setTimeout(() => { administracionFacturacionGestion('.$data["idCompañia"].') }, 1750)</script>';
                                }else{
                                    Sistema::debug('error', 'administracion.class.php - clienteFacturacionRegistro - Error al registrar la información del formulario. Ref.: '.DataBase::getError());
                                    $mensaje['tipo'] = 'danger';
                                    $mensaje['cuerpo'] = 'Hubo un error al registrar la facturación del cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                                    Alert::mensaje($mensaje);
                                }
                            }else{
                                Sistema::debug('error', 'administracion.class.php - clienteFacturacionRegistro - Error al registrar archivo. Ref.: '.$data["response"][0]["file"]);
                                $mensaje['tipo'] = 'danger';
                                $mensaje['cuerpo'] = 'Hubo un error al registrar el archivo '.$data["response"][0]["file"].'. <b>Intente nuevamente o contacte al administrador.</b>';
                                Alert::mensaje($mensaje);
                            }
                        }else{
                            Sistema::debug('error', 'administracion.class.php - clienteFacturacionRegistro - Error con los datos de la compañía. Ref.: '.$data["idCompañia"]." - ".$data["idCompañia2"]);
                            $mensaje['tipo'] = 'warning';
                            $mensaje['cuerpo'] = 'Hubo un error al comprobar la información de la compañía. <b>Intente nuevamente o contacte al administrador.</b>';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        Sistema::debug('error', 'administracion.class.php - clienteFacturacionRegistro - Error con los datos recibidos. Ref.: '.count($data));
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error con la información recibida. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    Sistema::debug('error', 'administracion.class.php - clienteFacturacionRegistro - Acceso denegado.');
                }
            }else{
                Sistema::debug('error', 'administracion.class.php - clienteFacturacionRegistro - Usuario no logueado.');
            }
        }

        public static function clienteFacturacionFormulario($idCompañia){
            if(Sistema::usuarioLogueado()){ 
                Session::iniciar();
                if($_SESSION["usuario"]->isAdmin()){
                    if(isset($idCompañia) && is_numeric($idCompañia) && $idCompañia > 0){
                        if(Compania::corroboraExistencia($idCompañia)){
                            ?>
                            <div id="administracion-cliente-facturacion-formulario-container">
                                <div class="d-flex justify-content-between">
                                    <div class="titulo">Registro nueva factura <?php echo $_SESSION["lista"]["compañia"][$idCompañia]["nombre"] ?></div>
                                    <button type="button" onclick="$('#administracion-cliente-facturacion-formulario-container').remove()" class="btn delete"><i class="fa fa-times"></i></button>
                                </div>
                                <div class="p-1">
                                    <div id="administracion-cliente-facturacion-process" style="display: none"></div>
                                    <form id="administracion-cliente-facturacion-form" action="./engine/administracion/cliente/facturacion-registro.php" form="#administracion-cliente-facturacion-form" process="#administracion-cliente-facturacion-process">
                                    <div class="form-group">
                                            <label class="col-form-label" for="recibo">Recibo</label>
                                            <input type="text" class="form-control" placeholder="Código de Recibo" id="recibo" name="recibo" value="FCA">
                                        </div>
                                        <div class="form-group d-none">
                                            <label class="col-form-label d-none" for="recibo">Identificador compañía</label>
                                            <input type="text" class="form-control d-none" id="idCompañia" name="idCompañia" value="<?php echo $idCompañia ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Total</label>
                                            <div class="form-group">
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="text" class="form-control" placeholder="0" id="total" name="total">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="file">Factura</label>
                                            <input type="file" class="form-control-file" id="file" name="file" aria-describedby="fileHelp">
                                            <small id="fileHelp" class="form-text text-muted">Archivo PDF de las facturas con sus respectivas copias</small>
                                        </div>
                                        <button type="button" onclick="administracionClienteFacturacionRegistro(<?php echo $idCompañia ?>)" class="btn btn-success">Registrar</button>
                                    </form>
                                </div>
                            </div>
                            <?php
                        }else{
                            Sistema::debug('info', 'administracion.class.php - clienteFacturacionFormulario - No se encontró la compañía con el identificador brindado. Ref.: '.$idCompañia);
                            $mensaje['tipo'] = 'info';
                            $mensaje['cuerpo'] = 'No se encontró la compañía.';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        Sistema::debug('error', 'administracion.class.php - clienteFacturacionFormulario - Identificador de oficina erroneo. Ref.: '.$idCompañia);
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al recibir la información de la compañía. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    Sistema::debug('error', 'administracion.class.php - clienteFacturacionFormulario - Acceso denegado.');
                }
            }else{
                Sistema::debug('error', 'administracion.class.php - clienteFacturacionFormulario - Usuario no logueado.');
            }
        }

        public static function clienteFacturacionGestion($idCompañia){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                if($_SESSION["usuario"]->isAdmin()){
                    if(isset($idCompañia) && is_numeric($idCompañia) && $idCompañia > 0){
                        if(Compania::corroboraExistencia($idCompañia)){
                            $data = Administracion::clienteFacturacionData($idCompañia);
                            if(is_array($data)){
                                ?>
                                <div>
                                    <div class="d-flex justify-content-between">
                                        <div class="titulo">Facturación</div>
                                        <button type="button" onclick="administracionClienteFacturacionFormulario(<?php echo $idCompañia ?>)" class="btn btn-success"><i class="fa fa-plus"></i></button>
                                    </div>
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
                                                    <td colspan="5" class="text-center">El cliente no tiene facturación asociada.</td>
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
                                Sistema::debug('error', 'administracion.class.php - clienteFacturacionGestion - Hubo un error al recibir la información de la facturación.');
                                $mensaje['tipo'] = 'warning';
                                $mensaje['cuerpo'] = 'Hubo un error al recibir la información de la facturación de la compañía. <b>Intente nuevamente o contacte al administrador.</b>';
                                Alert::mensaje($mensaje);
                            }
                        }else{
                            Sistema::debug('info', 'administracion.class.php - clienteFacturacionGestion - No se encontró la compañía con el identificador brindado. Ref.: '.$idCompañia);
                            $mensaje['tipo'] = 'info';
                            $mensaje['cuerpo'] = 'No se encontró la compañía.';
                            Alert::mensaje($mensaje);
                        }
                    }else{
                        Sistema::debug('error', 'administracion.class.php - clienteFacturacionGestion - Identificador de oficina erroneo. Ref.: '.$idCompañia);
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al recibir la información de la compañía. <b>Intente nuevamente o contacte al administrador.</b>';
                        Alert::mensaje($mensaje);
                    }
                }else{
                    Sistema::debug('error', 'administracion.class.php - clienteFacturacionGestion - Acceso denegado.');
                }
            }else{
                Sistema::debug('error', 'administracion.class.php - clienteFacturacionGestion - Usuario no logueado.');
            }
        }

        public static function clienteGestion($data){
            if(Sistema::usuarioLogueado()){ 
                Session::iniciar();
                if($_SESSION["usuario"]->isAdmin()){
                    ?>
                    <div class="mine-container">
                        <nav class="navbar navbar-expand navbar-dark bg-red-1">
                            <a class="navbar-brand" href="#"><img src="./image/compañia/<?php echo $data["compania"] ?>/logo.png" height="35" /> <?php echo $_SESSION["lista"]["compañia"][$data["compania"]]["nombre"] ?></a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCompania" aria-controls="navbarCompania" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>

                            <div class="collapse navbar-collapse" id="navbarCompania">
                                <ul class="navbar-nav mr-auto">
                                    <li class="nav-item active">
                                        <a class="nav-link" href="#/"><i class="fa fa-home"></i> Dashboard <span class="sr-only">(current)</span></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#/"><i class="fa fa-bar-chart"></i> Estadísticas</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#/" id="navbarCompaniaDD1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-cogs"></i> Administración</a>
                                        <div class="dropdown-menu" aria-labelledby="navbarCompaniaDD1">
                                            <a class="dropdown-item" href="#/"><i class="fa fa-users"></i> Usuarios</a>
                                            <a class="dropdown-item" href="#/"><i class="fa fa-building"></i> Sucursales</a>
                                            <a class="dropdown-item" href="#/" onclick="administracionFacturacionGestion(<?php echo $data['compania'] ?>)"><i class="fa fa-file-pdf-o"></i> Facturación</a>
                                        </div>
                                    </li>
                                </ul>
                                <form class="form-inline my-2 my-md-0">
                                    <input class="form-control" type="text" placeholder="Search">
                                </form>
                            </div>
                        </nav>
                        <div id="administracion-cliente-process" class="p-2"></div>
                    </div>
                    <?php
                }else{
                    Sistema::debug('error', 'administracion.class.php - clienteGestion - Acceso denegado.');
                }
            }else{
                Sistema::debug('error', 'administracion.class.php - clienteGestion - Usuario no logueado.');
            }
        }

        public static function productoGestionar(){ 
            if(Sistema::usuarioLogueado() && $_SESSION["usuario"]->isAdmin()){
            ?>
                <div class="mine-container">
                    <div id="gestio-producto-process"></div>
                    <div> 
                        <div class="titulo">Seleccione Compañí­a</div>
                        <form id="gestio-producto-form" action="./includes/compania/buscaFormulario.php" form="#gestion-producto-form" process="#gestion-producto-process">
                            
                        </form>
                    </div>
                </div>
            <?php     
            }else{
                Sistema::debug('error', ' admin.class - productoGestionar - No tiene permiso.');
            } 
        } 

        public static function clienteUsuarioAgregarFormulario(){ 
            if(Sistema::usuarioLogueado() && $_SESSION["usuario"]->isAdmin()){
            ?>
                <div class="mine-container">
                    <div id="nueva-compania-process"></div>
                    <div> 
                        <div class="titulo">Nueva Compañía</div>
                        <form id="nueva-compania-form" action="./engine/administracion/cliente/nuevaCompania.php" form="#nueva-compania-form" process="#nueva-compania-process">
                            <div class="form-group">
                                <label class="col-form-label required" required for="nombre"><i class="fa fa-user-circle"></i> Nombre</label>
                                <input type="nombre" class="form-control" required placeholder="Nombre cliente" id="nombre" name="nombre"> 
                                <button type="button" class="btn btn-success" onclick="nuevaCompania()">Guardar</button>
                            </div> 
                        </form> 
                    </div>    
                </div>
            <?php     
            }else{
                Sistema::debug('error', ' admin.class - clienteGestionar - No tiene permiso.');
            } 
        }

        public static function getListaUsuario($compania)
        { 
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                $query = DataBase::select("usuario","nombre,email,sucursal,rol,estado","compañia = '".$compania."'","ORDER BY sucursal ASC");
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
                    Sistema::debug('error', 'admin.class.php - getListaUsuario - Error al buscar información de compañía. Ref.: '.DataBase::getError());
                }
            }else{
                Sistema::debug('error', 'admin.class.php - getListaUsuario - Usuario no logueado.');
            }
            return false; 
        }  

        public static function visualizarUsuario($compania)
        {
            $lista = Admin::getListaUsuario($compania);
            ?>
            <div class="mine-container">
                <div class="d-flex justify-content-between"> 
                    <div class="titulo">Lista de Usuarios</div>
                    <button type="button" class="btn btn-primary"><i class="fa fa-plus"></i>Usuario</button>
                </div>
                <div class="p-1">
                    <table id="tabla-usuarios" class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Email</th>
                                <th class="text-center" scope="col">Sucursal</th>
                                <th class="text-center" scope="col">Rol</th>
                                <th class="text-center" scope="col">Estado</th> 
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            if(is_array($lista)){
                                if(count($lista) > 0){ 
                                    $sucursal = $_SESSION["lista"]["sucursal"];
                                    $rol = $_SESSION["lista"]["usuario"]["rol"];
                                    $counter = 0;
                                    foreach($lista AS $key => $value){
                                        ?>
                                        <tr id="usuario-<?php echo $key ?>" lista-key="<?php echo $key ?>"> 
                                            <td><?php echo $value["nombre"] ?></td>
                                            <td><?php echo $value["email"] ?></td>
                                            <td><?php echo $sucursal[$value["sucursal"]]["nombre"] ?></td> 
                                            <td><?php echo $rol[$value["rol"]]["rol"]; ?></td> 
                                            <td><?php echo $value["estado"] ?></td>
                                        </tr>
                                        <?php
                                        $counter++;
                                        if($counter == 500){
                                            ?>
                                            <tr>
                                                <td colspan="9" class="text-center">
                                                    Cargar mÃ¡s
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
                                            No se encontraron usuarios registrados en la compañía. Para cargar un nuevo usuario clickee en el siguiente <a href="#/" onclick="">link</a>.
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
                                if(is_bool($lista) && !$lista){
                                    Sistema::debug('error', 'admin.class.php - visualizaUsuario - Data boolean FALSE.');
                                }else{
                                    Sistema::debug('error', 'admin.class.php - visualizarUsuario - Error desconocido.');
                                }
                                ?> 
                                <tr>
                                    <td colspan="9" class="text-center">
                                        Hubo un error al encontrar los usuarios de la compañía. <b>Intente nuevamente o contacte al administrador.</b>
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
            <?php
        }

        public static function setNuevaCompania($data){ 
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    $carga = date("Y-m-d H:i:s");
                    $query = DataBase::insert("compañia", "nombre", "'".$data["nombre"]."'");
                    if($query){ 
                        Sistema::debug("success", "admin.class.php - setNuevaCompania - compañía registrada satisfactoriamente.");
                        $mensaje['tipo'] = 'success';
                        $mensaje['cuerpo'] = 'Se registró la compañía <b>'.$data["nombre"].'</b> satisfactoriamente.'; 
                        Alert::mensaje($mensaje);
                        return true;
                    }else{
                        Sistema::debug('error', 'admin.class.php - setNuevaCompania - Error en query de registro de compañía.');
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al registrar la compañía. <b>Intente nuevamente o contacte al administrador.</b>'; 
                        Alert::mensaje($mensaje);
                        return false;
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug("error", "admin.class.php - setNuevaCompania - Arreglo de datos del formulario incorrecto.");
                }
            }else{
                Sistema::debug('error', 'admin.class.php - setNuevaCompania - Usuario no logueado.');
            } 
        } 
    }      
?>