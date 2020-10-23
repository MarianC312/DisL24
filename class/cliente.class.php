<?php
class Cliente
    {
        public static function registroFormulario()
        { 
            if(Sistema::usuarioLogueado())
            {
            ?>
                <div class="mine-container">
                    <div id="cliente-registro-process"></div>
                    <div > 
                        <div class="titulo">Registro de Clientes Nuevos</div> 
                        <form id="cliente-registro-form" action="./engine/cliente/registro.php" form="#cliente-registro-form" process="#cliente-registro-process">
                            <div class="form-group">
                                <label class="col-form-label required" required for="nombre"><i class="fa fa-user-circle"></i> Nombre y Apellido</label>
                                <input type="nombre" class="form-control" required placeholder="Nombre cliente" id="nombre" name="nombre"> 
                                <label class="col-form-label required" required for="documento"><i class="fa fa-address-card"></i> Documento</label>
                                <input type="documento" class="form-control" required placeholder="Documento cliente" id="documento" name="documento">  
                                <label class="col-form-label required" required for="telefono"><i class="fa fa-phone-square"></i> Teléfono</label>
                                <input type="telefono" class="form-control" required placeholder="Telefono cliente" id="telefono" name="telefono">
                                <label class="col-form-label" required for="domicilio"><i class="fa fa-home"></i> Domicilio</label>
                                <input type="domicilio" class="form-control" placeholder="Domicilio cliente" id="domilicio" name="domicilio"> 
                                <label class="col-form-label" for="email"><i class="fa fa-envelope-square"></i> Email</label>
                                <input type="email" class="form-control" placeholder="Email cliente" id="email" name="email"> 
                                <input type="estado" class="form-control" style="display: none;" id="estado" value="1" name="estado">
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-success" onclick="clienteRegistro()">Guardar Cliente</button>
                            </div>
                        </form>
                        <div> 
                            <div class="titulo">Edición de Clientes ya cargados</div>
                            <div id="cliente-edita-process"></div>
                            <form id="cliente-edita-form" action="./includes/cliente/edicion.php" form="#cliente-edita-form" process="#cliente-edita-process"> 
                            <label class="col-form-label required" required for="documento"><i class="fa fa-address-card"></i> Documento</label>
                            <input type="documento" class="form-control" required placeholder="Documento cliente" id="documento" name="documento"> 
                            <button type="button" onclick="clienteEditarFormulario()" class="btn btn-success">Editar cliente</button>
                            </form>
                        </div> 
                    </div>
                </div>
            <?php     
            }else{
                Sistema::debug('', ' - Usuario no logueado.');
            } 
        }

        public static function registro($data)
        {
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    $productoExiste = Cliente::corroboraExistencia(["documento" => $data["documento"]]);
                    if($productoExiste){
                        Sistema::debug("info", "cliente.class.php - registro - El cliente ya existe en la base de datos.");
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'El cliente ya se encuentra registrado.';
                        $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around"> 
                            <button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-outline-info">Regresar</button>
                        </div>';
                        Alert::mensaje($mensaje);
                        return;
                    }else{
                        $query = DataBase::insert("cliente", "nombre,documento,telefono,domicilio,email,sucursal,compañia,estado", "'".$data["nombre"]."','".$data["documento"]."','".$data["telefono"]."','".((isset($data["domicilio"])) ? $data["domicilio"] : "NULL")."','".((isset($data["email"])) ? $data["email"] : "NULL")."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."', '".$data["estado"]."'");
                        if($query){
                            Session::iniciar();
                            $_SESSION["usuario"]->tareaEliminar('Registro de cliente ['.$data["documento"].']');
                            Sistema::debug("success", "cliente.class.php - registro - cliente registrado satisfactoriamente.");
                            $mensaje['tipo'] = 'success';
                            $mensaje['cuerpo'] = 'Se registró el cliente <b>'.$data["nombre"].'</b> satisfactoriamente.';
                            $mensaje['cuerpo'] .= '<div class="d-flex justify-content-around">
                                <button type="button" onclick="clienteRegistroFormulario()" class="btn btn-success">Registrar otro cliente</button> 
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
                if(isset($data) && is_array($data) && (count($data) == 4 || count($data) == 1)){
                    if(isset($data["documento"]) && is_numeric($data["documento"]) && $data["documento"] > 0){
                        $query = DataBase::select("cliente", "nombre,documento", "documento = '".$data["documento"]."'", "");
                        if($query){
                            if(DataBase::getNumRows($query) == 1){
                                $dataQuery = DataBase::getArray($query);
                                if($cargaFormularioRegistro){
                                    Sistema::debug("success", "cliente.class.php - corroboraExistencia - Cliente encontrado, carga de formulario de edición para cliente: ".$dataQuery["nombre"].".");
                                    Cliente::editarFormulario($dataQuery["documento"]);
                                }else{
                                    Sistema::debug("success", "cliente.class.php - corroboraExistencia - Cliente encontrado: ".$dataQuery["nombre"].".");
                                    return true;
                                }
                            }else{ 
                                if($cargaFormularioRegistro){
                                    echo '<script>clienteRegistroFormulario()</script>';
                                    Sistema::debug("success", "cliente.class.php - corroboraExistencia - Cliente inexistente, cargando formulario de registro.");
                                }else{
                                    Sistema::debug("success", "cliente.class.php - corroboraExistencia - Cliente inexistente.");
                                    return false;
                                }
                            }
                        }else{
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al comprobar la información del cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                            Sistema::debug("error", "cliente.class.php - corroboraExistencia - Error en query de comprobación de información.");
                        }
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'El documento ingresado es incorrecto. Debe ser un número. <b>Intente nuevamente.</b>';
                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                        Alert::mensaje($mensaje);
                        Sistema::debug("error", "cliente.class.php - corroboraExistencia - Documento no numérico.");
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al recibir la información del cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug("error", "cliente.class.php - corroboraExistencia - No se recibió la data correcta.");
                }
            }else{
                Sistema::debug('error', 'cliente.class.php - corroboraExistencia - Usuario no logueado.');
            }
        }

        public static function editarFormulario($documento){ 
            if(Sistema::usuarioLogueado()){
                if(isset($documento) && is_numeric($documento) && $documento > 0){
                    $query = DataBase::select("cliente","*","documento = '".$documento."'","");
                        if($query){
                            if(DataBase::getNumRows($query) == 1){
                                $dataQuery = DataBase::getArray($query);
                                ?>
                                <div class="mine-container">  
                                    <div id="cliente-edicion-process"></div>
                                    <form id="cliente-edicion-form" action="./engine/cliente/edicion.php" form="#cliente-edicion-form" process="#cliente-edicion-process">
                                        <div class="form-group">
                                            <label class="col-form-label required" required for="nombre" ><i class="fa fa-user-circle"></i> Nombre y Apellido</label>
                                            <input type="nombre" class="form-control" required value="<?php echo $dataQuery["nombre"]?>" id="nombre" name="nombre"> 
                                            <label class="col-form-label required" required for="documento"><i class="fa fa-address-card"></i> Documento</label>
                                            <input type="documento" class="form-control" required value="<?php echo $dataQuery["documento"]?>" id="documento" name="documento">  
                                            <label class="col-form-label required" required for="telefono"><i class="fa fa-phone-square"></i> Teléfono</label>
                                            <input type="telefono" class="form-control" required value="<?php echo $dataQuery["telefono"]?>" id="telefono" name="telefono">
                                            <label class="col-form-label" required for="domicilio"><i class="fa fa-home"></i> Domicilio</label>
                                            <input type="domicilio" class="form-control" value="<?php echo $dataQuery["domicilio"]?>" id="domilicio" name="domicilio"> 
                                            <label class="col-form-label" for="email"><i class="fa fa-envelope-square"></i> Email</label>
                                            <input type="email" class="form-control" value="<?php echo $dataQuery["email"]?>" id="email" name="email">
                                            <label for="estado"><i class="fa fa-list-alt"></i> Dar de baja</label>
                                            <select class="form-control" id="estado" name="estado">
                                                <option value="1" selected> Activo </option>
                                                <option value="0"> Desactivo </option>
                                            </select> 
                                            <input type="idCliente" class="form-control" value="<?php echo $dataQuery["id"]?>" id="idCliente" name="idCliente" style="display: none;">
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-success" onclick="clienteEdicion()">Editar Cliente</button>
                                        </div>
                                    </form> 
                                </div>
                                <?php     
                                }else{ 
                                    Sistema::debug("error", "cliente.class.php - editaFormulario - Error en query de comprobación de información.");
                                }
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

        public static function edicion($data)
        {
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0)
                {
                    $query = DataBase::update("cliente","nombre = '".$data["nombre"]."', documento = '".$data["documento"]."', telefono = '".$data["telefono"]."', domicilio = '".((isset($data["domicilio"])) ? $data["domicilio"] : "NULL")."', email = '".((isset($data["email"])) ? $data["email"] : "NULL")."', estado = '".$data["estado"]."'","id = '".$data["idCliente"]."'");
                        if($query){
                            Session::iniciar();
                            $_SESSION["usuario"]->tareaEliminar('Edición de cliente ['.$data["documento"].']');
                            Sistema::debug("success", "cliente.class.php - edicion - cliente editado satisfactoriamente.");
                            $mensaje['tipo'] = 'success';
                            $mensaje['cuerpo'] = 'Se editó el cliente <b>'.$data["nombre"].'</b> satisfactoriamente.'; 
                            Alert::mensaje($mensaje);
                            return true;
                        }else{
                            Sistema::debug('error', 'cliente.class.php - edicion - Error en query de edición de cliente.');
                            $mensaje['tipo'] = 'danger';
                            $mensaje['cuerpo'] = 'Hubo un error al editar el cliente. <b>Intente nuevamente o contacte al administrador.</b>';
                            $mensaje['cuerpo'] .= '<div class="d-block"><button type="button" onclick="$(\''.$data['form'].'\').show(350);$(\''.$data['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                            Alert::mensaje($mensaje);
                            return false;
                        }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. <b>Intente nuevamente o contacte al administrador.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug("error", "cliente.class.php - edicion - Arreglo de datos del formulario incorrecto.");
                }
            }else{
                Sistema::debug('', ' - Usuario no logueado.');
            }
        }
    }
?>