<?php
    class Caja{
        public static function accionRegistrar($data){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && count($data) > 0){
                    Session::iniciar();
                    $query = DataBase::insert("compañia_sucursal_caja_historial", "tipo,observacion,monto,operador,sucursal,compañia", "'".$data["tipo"]."','".$data["observacion"]."','".$data["monto"]."','".$_SESSION["usuario"]->getId()."','".$_SESSION["usuario"]->getSucursal()."','".$_SESSION["usuario"]->getCompañia()."'");
                    if($query){
                        $mensaje['tipo'] = 'success';
                        $mensaje['cuerpo'] = 'Se registró el movimiento satisfactoriamente.';
                        Alert::mensaje($mensaje);
                    }else{
                        $mensaje['tipo'] = 'danger';
                        $mensaje['cuerpo'] = 'Hubo un error al registrar el movimiento en caja. <b>Intente nuevamente o contacte al administrador.</b>';
                        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                        Alert::mensaje($mensaje);
                        Sistema::debug('error', 'caja.class.php - accionRegistrar - Error al registrar la información en base de datos. Ref.: '.DataBase::getError());
                    }
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error con la información recibida. <b>Intente nuevamente o contacte al administrador.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('error', 'caja.class.php - accionRegistrar - Error en el arreglo de datos.');
                }
            }else{
                Sistema::debug('error', 'caja.class.php - accionRegistrar - Usuario no logueado.');
            }
        }

        public static function accionRegistrarFormulario(){
            if(Sistema::usuarioLogueado()){
                $accion = $_SESSION["lista"]["caja"]["accion"]["tipo"];
                ?>
                <div id="container-caja-accion-formulario" class="mine-container">
                    <div class="d-flex justify-content-between"> 
                        <div class="titulo">Acción en caja</div>
                        <button type="button" onclick="$('#container-caja-accion-formulario').remove()" class="btn delete"><i class="fa fa-times"></i></button>
                    </div>
                    <div id="caja-accion-process" style="display: none"></div>
                    <form id="caja-accion-form" action="./engine/caja/accion-registrar.php" form="#caja-accion-form" process="#caja-accion-process">
                        <div class="d-flex justify-content-around">
                            <div class="form-group mb-0 mr-1">
                                <label for="tipo">Tipo</label>
                                <select class="form-control" id="tipo" name="tipo">
                                    <?php
                                        if(is_array($accion) && count($accion) > 0){
                                            foreach($accion AS $key => $value){
                                                echo '<option value="'.$key.'">'.$value["accion"].'</option>';
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group flex-grow-1 mb-0 mr-1">
                                <label for="observacion">Observación</label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="1"></textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label class="control-label">Monto</label>
                                <div class="form-group mb-0">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="text" class="form-control" id="monto" name="monto" placeholder="0.00">
                                    </div>
                                    <small class="text-muted">Montos decimales con ".". (Separador de miles automático)</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group d-flex justify-content-right">
                            <button type="button" onclick="cajaAccionRegistrar()" class="btn btn-success">Registrar</button>
                        </div>
                    </form>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'caja.class.php - accionFormulario - Usuario no logueado.');
            }
        }

        public static function historial(){
            if(Sistema::usuarioLogueado()){
                $data = [];
                if(is_array($data)){
                    ?>
                    <div class="titulo">Historial de caja</div>
                    <div class="p-1">
                        <table id="tabla-caja-historial" class="table table-hover w-100">
                            <thead>
                                <tr>
                                    <td>Historial</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(count($data) > 0){
                                        foreach($data AS $key => $value){
                                            ?>
                                            <tr>
                                                <td></td>
                                            </tr>
                                            <?php
                                        }
                                    }else{
                                        ?>
                                        <tr>
                                            <td class="text-center">No se encontraron registros.</td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <script>
                        dataTableSet("#tabla-caja-historial", false, [[5, 10, 25, 50, 100, -1],[5, 10, 25, 50, 100, "Todos"]], 10);
                    </script>
                    <?php
                }else{
                    Sistema::debug('error', 'caja.class.php - historial - Error en los datos recibidos de la caja.');
                }
            }else{
                Sistema::debug('error', 'caja.class.php - historial - Usuario no logueado.');
            }
        }

        public static function gestion(){
            if(Sistema::usuarioLogueado()){
                Session::iniciar();
                ?>
                <div class="mine-container">
                    <div class="d-flex justify-content-between">
                        <div class="titulo"><?php echo mb_strtoupper($_SESSION["usuario"]->getInfo(null, "sucursal")) ?> - Gestión de caja</div>
                        <div class="btn-group">
                            <button type="button" onclick="cajaAccionRegistrarFormulario()" class="btn btn-outline-info"><i class="fa fa-plus"></i> movimiento</button>
                            <button type="button" onclick="ventaRegistrarFormulario()" class="btn btn-outline-info"><i class="fa fa-plus"></i> venta</button>
                        </div>
                    </div>
                    <div id="container-caja-accion"></div>
                    <div id="container-caja-historial" class="p-1">
                        <?php Caja::historial() ?>
                    </div>
                </div>
                <?php
            }else{
                Sistema::debug('error', 'caja.class - gestion - Usuario no logueado.');
            }
        }
    }
?>