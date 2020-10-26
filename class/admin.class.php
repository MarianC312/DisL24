<?php
class Admin
{
    public static function gestionUsuario()
    { 
        if(Sistema::usuarioLogueado() && $_SESSION["usuario"]->isAdmin())
            {
            ?>
                <div class="mine-container">
                    <div id="gestion-usuario-process"></div>
                    <div> 
                        <?php
                            Compania::buscarFormulario();
                        ?>
                    </div>
                </div>
            <?php     
            }else{
                Sistema::debug('error', ' admin.class - gestionUsuario - No tiene permiso.');
            } 
    } 

    public static function gestionProducto()
    { 
        if(Sistema::usuarioLogueado() && $_SESSION["usuario"]->isAdmin())
        {
        ?>
            <div class="mine-container">
                <div id="gestio-producto-process"></div>
                <div> 
                    <div class="titulo">Seleccione Compañía</div>
                    <form id="gestio-producto-form" action="./includes/compañia/buscaFormulario.php" form="#gestion-producto-form" process="#gestion-producto-process">
                        
                    </form>
                </div>
            </div>
        <?php     
        }else{
            Sistema::debug('error', ' admin.class - gestionProducto - No tiene permiso.');
        } 
    } 

    public static function gestionCliente()
    { 
        if(Sistema::usuarioLogueado() && $_SESSION["usuario"]->isAdmin())
        {
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
            Sistema::debug('error', ' admin.class - gestionCliente - No tiene permiso.');
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
                                        No se encontraron usuarios registrados en la compañia. Para cargar un nuevo usuario clickee en el siguiente <a href="#/" onclick="">link</a>.
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

    public static function setNuevaCompania($data)
    { 
        if(Sistema::usuarioLogueado()){
            if(isset($data) && is_array($data) && count($data) > 0)
            {
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