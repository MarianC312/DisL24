<?php
    class Producto{
        public static function corroboraExistencia($data, $cargaFormularioRegistro = false){
            if(Sistema::usuarioLogueado()){
                if(isset($data) && is_array($data) && (count($data) == 4 || count($data) == 1)){
                    if(isset($data["codigo"]) && is_numeric($data["codigo"]) && $data["codigo"] > 0){
                        $query = DataBase::select("producto", "id", "codigoBarra = '".$data["codigo"]."'", "");
                        if($query){
                            if(DataBase::getNumRows($query) == 1){
                                $dataQuery = DataBase::getArray($query);
                                Producto::editarFormulario($dataQuery["id"]);
                                if($cargaFormularioRegistro){
                                    Sistema::debug("success", "producto.class.php - corroboraExistencia - Producto encontrado, carga de formulario de edición para producto ID: ".$dataQuery["id"].".");
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

        public static function registroFormulario($corroborar = true, $codigo = 0){
            if(Sistema::usuarioLogueado()){
                if($corroborar){
                    Producto::corroboraExistenciaFormulario();
                }else{
                    ?>
                    <div class="mine-container">
                        <div class="titulo">Registrar producto</div>
                        <div id="producto-registro-formulario-process" style="display: none;"></div>
                        <form id="producto-registro-formulario-form" action="./engine/producto/registro.php" form="#producto-registro-formulario-form" process="#producto-registro-formulario-process"> 
                            <div class="form-group">
                                <label class="col-form-label" for="codigo"><i class="fa fa-barcode"></i> Código</label>
                                <input type="text" class="form-control" placeholder="Código comercial del producto" id="codigo" name="codigo" value="<?php echo (is_numeric($codigo) && $codigo > 0) ? $codigo : ''; ?>">
                            </div>
                        </form>
                    </div>
                    <?php
                }
            }else{
                Sistema::debug("error", "producto.class.php - registroFormulario - Usuario no logueado.");
            }
        }
    }
?>