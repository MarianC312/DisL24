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
                        }else{
                            $data = $_SESSION["tarea"][$data["tarea"]]["data"];
                        }
                        ?>
                        <div class="mine-container">
                            <div class="titulo">Registro de producto</div>
                            <div id="producto-registro-formulario-process" style="display: none;"></div>
                            <div id="producto-registro-stepper-1" class="bs-stepper">
                                <div class="bs-stepper-header" role="tablist">
                                    <div class="step" data-target="#producto-registro-p-1">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-1" id="producto-registro-p-1-trigger">
                                            <span class="bs-stepper-circle">1</span>
                                            <span class="bs-stepper-label">Datos obligatorios</span>
                                        </button>
                                    </div>
                                    <div class="line"></div>
                                    <div class="step" data-target="#producto-registro-p-2">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-2" id="producto-registro-p-2-trigger">
                                            <span class="bs-stepper-circle">2</span>
                                            <span class="bs-stepper-label">Datos opcionales</span>
                                        </button>
                                    </div>
                                    <div class="line"></div>
                                    <div class="step" data-target="#producto-registro-p-3">
                                        <button type="button" class="step-trigger" role="tab" aria-controls="producto-registro-p-3" id="producto-registro-p-3-trigger">
                                            <span class="bs-stepper-circle">3</span>
                                            <span class="bs-stepper-label">Completar registro</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="bs-stepper-content">
                                    <form id="producto-registro-formulario-form" onsubmit="return false" action="./engine/producto/registro.php" form="#producto-registro-formulario-form" process="#producto-registro-formulario-process"> 
                                        <div id="producto-registro-p-1" class="content" role="tabpanel" aria-labelledby="producto-registro-p-1-trigger">
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
                                                <button class="btn btn-outline-primary" onclick="stepper1.next()">Siguiente</button>
                                            </div>
                                        </div>
                                        
                                        <div id="producto-registro-p-2" class="content" role="tabpanel" aria-labelledby="producto-registro-p-2-trigger">
    
                                            <div class="form-group d-flex justify-content-between"> 
                                                <button class="btn btn-outline-primary" onclick="stepper1.previous()">Anterior</button>
                                                <button class="btn btn-outline-primary" onclick="stepper1.next()">Siguiente</button>
                                            </div>
                                        </div>
                                        
                                        <div id="producto-registro-p-3" class="content" role="tabpanel" aria-labelledby="producto-registro-p-3-trigger">
                                            <div class="form-group d-flex justify-content-between"> 
                                                <button class="btn btn-outline-primary" onclick="stepper1.previous()">Anterior</button>
                                                <button class="btn btn-outline-success" onclick="stepper1.next()">Registrar producto</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <script> 
                                $("#producto-registro-formulario-form :input").on('focusout', (e) => {
                                    tareaAgregarData('Registro de producto [<?php echo $data["codigo"] ?>]', e.currentTarget.id, e.currentTarget.value, '#producto-registro-formulario-process');
                                });
                                $("#producto-registro-formulario-form select").on('change', (e) => {
                                    tareaAgregarData('Registro de producto [<?php echo $data["codigo"] ?>]', e.currentTarget.id, e.currentTarget.value, '#producto-registro-formulario-process');
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
                                tippy('#producto-categoria-get-form', {
                                    content: 'Agregar una nueva categoría de producto a la lista.',
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