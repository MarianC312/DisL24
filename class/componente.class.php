<?php
    class Componente{
        public static function headerUsuarioAlert($id, $url, $icon, $test = false){
            if($test){
                $cantidad = rand(8, 55);
            }else{ 
                $data = Sistema::alertGetData($id);
                if(is_array($data)){
                    $cantidad = count($data);
                }else{
                    Sistema::debug("error", "componente.class.php - headerUsuarioAlert - Data no es un arreglo.");
                }
            }
            ?>
            <div class="dropdown bg-orange mr-2">
                <button type="button" class="btn user clear" id="header-usuario-alerta-<?php echo $id ?>-dropdownMenuButtonAlert" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-<?php echo $icon ?>"></i>
                    <span class="badge badge-pill badge-primary <?php echo (isset($cantidad) && is_numeric($cantidad) && $cantidad > 0) ? '' : 'd-none' ?>"><?php echo (isset($cantidad) && is_numeric($cantidad)) ? $cantidad : '' ?></span>
                </button>
                <div class="dropdown-menu" aria-labelledby="<?php echo $id ?>-dropdownMenuButtonAlert">
                    <?php
                        foreach($data AS $key => $value){
                            ?>
                            <div class="d-flex justify-content-between align-items-center p-2 w-100" style="min-height: 5em; cursor: pointer;">
                                <i class="fa fa-square-o"></i>
                                <div class="d-flex flex-column flex-grow-1 p-2">
                                    <div class="font-weight-bold text-slice d-block w-75"><?php echo (strlen($value["titulo"]) > 0) ? $value["titulo"] : "Sin título" ?></div>
                                    <div class=""><?php echo $value["cuerpo"] ?></div>
                                    <div class="text-muted d-flex justify-content-between">
                                        <span>Operador</span>
                                        <span><?php echo date("d/m/Y, H:i A", strtotime($value["carga"])) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    ?>
                </div> 
            </div>
            <?php
        }

        public static function headerUsuarioMain($data){ 
            Session::iniciar();
            ?>
            <div class="dropdown bg-orange">
                <button class="btn user clear" type="button" id="dropdownMenuButton-<?php echo $data["id"] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="mr-2" src="<?php echo ($data["compañiaId"] > 0) ? "image/compañia/".$data["compañiaId"]."/logo.png" : "image/logo-standalone.png" ?>" height="35" alt="<?php echo $data["compañia"] ?>" />
                    <div class="d-flex flex-column align-items-baseline mr-2">
                        <span class="user-name"><?php echo $data["nombre"] ?></span>
                        <span class="user-rol"><?php echo $data["compañia"]."<br>".$data["sucursal"]." - ".$data["rol"] ?></span>
                    </div>
                    <i class="fa fa-caret-down"></i>
                </button>
                <div class="dropdown-menu" style="min-width: auto" aria-labelledby="dropdownMenuButton-<?php echo $data["id"] ?>">
                    <a class="dropdown-item" href="#/"><i class="fa fa-cog"></i> Configurar cuenta</a>
                    <a class="dropdown-item" href="#/"><i class="fa fa-unlock-alt"></i> Cambiar contraseña</a>
                    <a class="dropdown-item" href="#/"><i class="fa fa-envelope"></i> Ver mensajes</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#/" onclick="requestLogout()"><i class="fa fa-sign-out"></i> Salir</a>
                </div> 
            </div>
            <?php
        }

        public static function usuarioTareasPendientesContent($data = null){
            Session::iniciar();
            $data = (is_array($data) && count($data) > 0) ? $data : $_SESSION["tarea"];
            if(is_array($data) && count($data) > 0){
                ?>
                <ul id="tareas-pendientes-lista" class="list-group list-group-flush">
                    <?php
                        $counter = 0;
                        foreach($_SESSION["tarea"] AS $key => $value){
                            ?>
                            <li id="tarea-pendiente-<?php echo $counter ?>" onmouseover="replaceClass('#tarea-pendiente-<?php echo $counter ?> div', 'd-none', 'fly')" onmouseout="replaceClass('#tarea-pendiente-<?php echo $counter ?> div', 'fly', 'd-none')" class="list-group-item d-flex justify-content-between align-items-center pr-5">
                                <span class="text-slice"><?php echo $key ?></span>
                                <div class="btn-group d-none" style="top: auto">
                                    <button type="button" id="tarea-pendiente-<?php echo $counter ?>-accion" onclick="<?php echo $value["data"]["accion"] ?>" class="btn btn-sm btn-info"><i class="fa fa-repeat"></i></button>
                                    <button type="button" id="tarea-pendiente-<?php echo $counter ?>-eliminar" onclick="tareaPendienteEliminar('<?php echo $key ?>', <?php echo $counter ?>)" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></button>
                                </div>
                            </li>
                            <script>
                                tippy('#tarea-pendiente-<?php echo $counter ?>-eliminar', {
                                    content: 'Eliminar esta tarea pendiente definitivamente.',
                                    delay: [150,150],
                                    animation: 'fade'
                                });
                                tippy('#tarea-pendiente-<?php echo $counter ?>-accion', {
                                    content: 'Continuar con esta tarea pendiente.',
                                    delay: [150,150],
                                    animation: 'fade'
                                });
                            </script>
                            <?php
                            $counter++;
                        }
                    ?>
                </ul>
                <?php
            }else{
                echo '<script>unloadUsuarioTareasPendientes()</script>';
            }
        } 

        public static function usuarioTareasPendientesHeader($data = null){
            Session::iniciar();
            $data = (is_array($data) && count($data) > 0) ? $data : $_SESSION["tarea"];
            $opcion = (isset($_SESSION["componente"]["tarea"]["pendiente"])) ? $_SESSION["componente"]["tarea"]["pendiente"] : [];
            if(count($data) < 1){
                echo '<script>unloadUsuarioTareasPendientes()</script>';
            }
            ?>
            <button type="button" id="tareas-pendientes-show" onclick="tareasPendientesSwap(this); tareasPendientesListaOption()" class="btn btn-sm btn-outline-white d-flex justify-content-between align-items-center w-100">
                <i class="fa <?php echo (isset($opcion["container"]) && $opcion["container"]) ? "fa-chevron-right" : "fa-chevron-left"; ?>"></i>
                <div class="titulo"><?php echo (is_array($data)) ? count($data)." tarea".((count($data) > 1) ? "s" : "")." pendiente".((count($data) > 1) ? "s" : "")."." : "[error]" ?></div>
            </button>
            <?php
        }

        public static function usuarioTareasPendientes(){
            Session::iniciar();
            $opcion = (isset($_SESSION["componente"]["tarea"]["pendiente"])) ? $_SESSION["componente"]["tarea"]["pendiente"] : [];
            if(!isset($_SESSION["tarea"]) || !is_array($_SESSION["tarea"]) || count($_SESSION["tarea"]) < 1){
                Sistema::debug("info", "componente.class.php - usuarioTareasPendientes - No existen tareas pendientes.");
                return null;
            }
            ?>
            <div id="container-tareas-pendientes" class="mine-container fly d-flex flex-column <?php echo (isset($opcion["container"]) && $opcion["container"]) ? "" : "small-container"; ?>">
                <div id="container-tareas-pendientes-header">
                    <?php Componente::usuarioTareasPendientesHeader() ?>
                </div>
                <div id="tareas-pendientes-process"></div> 
                <div id="container-tareas-pendientes-content" class="<?php echo (isset($opcion["container"]) && $opcion["container"]) ? "" : "d-none"; ?>" style="overflow: auto;"> 
                    <?php Componente::usuarioTareasPendientesContent(); ?>
                </div>
            </div>
            <script> 
                const tareasPendientesSwap = (e) => {
                    swapClass('#container-tareas-pendientes','small-container');
                    swapClass('#container-tareas-pendientes #container-tareas-pendientes-content','d-none');
                    ($("#" + e.id + " i").hasClass("fa-chevron-left")) ? replaceClass("#" + e.id + " i", "fa-chevron-left", "fa-chevron-right") : replaceClass("#" + e.id + " i", "fa-chevron-right", "fa-chevron-left");
                }
                const tareaPendienteEliminar = (tarea, idContainer) => {
                    var me = $(this);
                    if (me.data('requestRunning')) {
                        return;
                    }
                    me.data('requestRunning', true); 
                    let divProcess = "#tareas-pendientes-process";
                    let divForm = "";
                    $.ajax({
                        type: "POST",
                        url: "./engine/control/componente/tarea/pendiente/eliminar.php",
                        timeout: 45000,
                        beforeSend: function() {
                            //$(divProcess).load("./includes/loading.php");
                            //$(divForm).hide(350);
                            $(divProcess).show(350);
                        },
                        data: {tarea:tarea, idContainer:idContainer},
                        complete: function() {
                            me.data('requestRunning', false);
                        },
                        success: function(data) {
                            setTimeout(function() {
                                $(divProcess).html(data);
                            }, 1000);
                        }
                    }).fail(function(jqXHR) {
                        console.log(jqXHR.statusText);
                        me.data('requestRunning', false);
                    });
                } 

                const tareasPendientesLoadHeader = (callBack = true) => {
                    var me = $(this);
                    if (me.data('requestRunning')) {
                        return;
                    }
                    me.data('requestRunning', true); 
                    let divProcess = "#container-tareas-pendientes-header";
                    let divForm = "";
                    $.ajax({
                        type: "POST",
                        url: "./engine/control/componente/tarea/pendiente/header.php",
                        timeout: 45000,
                        beforeSend: function() {
                            //$(divProcess).load("./includes/loading.php");
                            //$(divForm).hide(350);
                            $(divProcess).show(350);
                        },
                        data: {},
                        complete: function() {
                            me.data('requestRunning', false);
                            (callBack) ? tareasPendientesLoadContent() : console.log('Info', 'componente.class.php - usuarioTareasPendientes - No se requirió carga de datos.');;
                        },
                        success: function(data) {
                            setTimeout(function() {
                                $(divProcess).html(data);
                            }, 1000);
                        }
                    }).fail(function(jqXHR) {
                        console.log(jqXHR.statusText);
                        me.data('requestRunning', false);
                    });
                }

                const tareasPendientesLoadContent = (data = null) => {
                    var me = $(this);
                    if (me.data('requestRunning')) {
                        return;
                    }
                    me.data('requestRunning', true); 
                    let divProcess = "#container-tareas-pendientes-content";
                    let divForm = "";
                    $.ajax({
                        type: "POST",
                        url: "./engine/control/componente/tarea/pendiente/contenido.php",
                        timeout: 45000,
                        beforeSend: function() {
                            //$(divProcess).load("./includes/loading.php");
                            //$(divForm).hide(350);
                            $(divProcess).show(350);
                        },
                        data: { data: data},
                        complete: function() {
                            me.data('requestRunning', false);
                        },
                        success: function(data) {
                            setTimeout(function() {
                                $(divProcess).html(data);
                            }, 1000);
                        }
                    }).fail(function(jqXHR) {
                        console.log(jqXHR.statusText);
                        me.data('requestRunning', false);
                    });
                }

                const tareasPendientesListaOption = () => {
                    var me = $(this);
                    if (me.data('requestRunning')) {
                        return;
                    }
                    me.data('requestRunning', true); 
                    let divProcess = "#tareas-pendientes-process";
                    let divForm = "";
                    $.ajax({
                        type: "POST",
                        url: "./engine/control/componente/tarea/pendiente/container/set-view.php",
                        timeout: 45000,
                        beforeSend: function() {
                            //$(divProcess).load("./includes/loading.php");
                            //$(divForm).hide(350);
                            $(divProcess).show(350);
                        },
                        data: {},
                        complete: function() {
                            me.data('requestRunning', false);
                        },
                        success: function(data) {
                            setTimeout(function() {
                                $(divProcess).html(data);
                            }, 1000);
                        }
                    }).fail(function(jqXHR) {
                        console.log(jqXHR.statusText);
                        me.data('requestRunning', false);
                    });
                }
            </script>
            <?php
        }

        public static function headerUsuario(){
            Session::iniciar();
            $data = $_SESSION["componente"]["header"]["usuario"]["data"]; 
            $dataMain = $_SESSION["usuario"]->getInfo();
            ?>
            <div class="header" name="<?php echo $data["nombre"] ?>" identificador="<?php echo $data["id"] ?>" version="<?php echo $data["version"] ?>" start="<?php echo $data["carga"] ?>">
                <?php
                    if($data["estado"]){ 
                        ?>
                        <div id="container-header-usuario">
                            <?php Componente::headerUsuarioMain($dataMain); ?>
                        </div>
                        <div class="d-flex">
                            <div id="container-header-usuario-alerta-1">
                                <?php Componente::headerUsuarioAlert("1", null, "bell") ?>
                            </div>
                            <div id="container-header-usuario-alerta-2">
                                <?php Componente::headerUsuarioAlert("2", null, "envelope") ?>
                            </div>
                        </div>
                        <?php
                    }else{
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'Módulo desactivado.';
                        Alert::mensaje($mensaje);
                    }
                ?>
            </div>
            <script>
                const headerUsuarioMainData = () => {
                    var me = $(this);
                    if (me.data('requestRunning')) {
                        return;
                    }
                    me.data('requestRunning', true);
                    let divProcess = "#container-header-usuario";
                    let divForm = "";
                    $.ajax({
                        type: "POST",
                        url: "./includes/componente/header-usuario.php",
                        timeout: 45000,
                        beforeSend: function() {
                            //$(divProcess).load("./includes/loading.php");
                            //$(divForm).hide(350);
                            $(divProcess).show(350);
                        },
                        data: {},
                        complete: function() {
                            me.data('requestRunning', false);
                        },
                        success: function(data) {
                            setTimeout(function() {
                                $(divProcess).html(data);
                            }, 1000);
                            setTimeout(() => {
                                headerUsuarioMainData();
                            }, (2 * 60 * 1000));
                        }
                    }).fail(function(jqXHR) {
                        console.log(jqXHR.statusText);
                        me.data('requestRunning', false);
                    });
                }
                const headerUsuarioAlerta = (id) => {
                    let me = {id: $(this)};
                    if (me.id.data('requestRunning')) {
                        return;
                    }
                    me.id.data('requestRunning', true);
                    let divProcess = "#container-header-usuario-alerta-" + id;
                    let divForm = "";
                    $.ajax({
                        type: "POST",
                        url: "./includes/componente/header-usuario-alerta.php",
                        timeout: 45000,
                        beforeSend: function() {
                            //$(divProcess).load("./includes/loading.php");
                            //$(divForm).hide(350);
                            $(divProcess).show(350);
                        },
                        data: {id:id},
                        complete: function() {
                            me.id.data('requestRunning', false);
                        },
                        success: function(data) {
                            setTimeout(function() {
                                $(divProcess).html(data);
                            }, 1000);
                            setTimeout(() => { headerUsuarioAlerta(id); }, (1 * 60 * 1000));
                        }
                    }).fail(function(jqXHR) {
                        console.log(jqXHR.statusText);
                        me.id.data('requestRunning', false);
                    });
                }
                setTimeout(() => { headerUsuarioMainData(); }, (2 * 60 * 1000));
                setTimeout(() => { headerUsuarioAlerta(1); }, (1 * 10 * 1000));
                setTimeout(() => { headerUsuarioAlerta(2); }, (1 * 5 * 1000));
                loadUsuarioTareasPendientes();
            </script>
            <?php
        }

        public static function menu(){
            Session::iniciar();
            $data = $_SESSION["componente"]["menu"]["data"];
            $opcion = (isset($_SESSION["componente"]["menu"]["opcion"])) ? $_SESSION["componente"]["menu"]["opcion"] : [];
            ?>
            <div class="w-100" name="<?php echo $data["nombre"] ?>" identificador="<?php echo $data["id"] ?>" version="<?php echo $data["version"] ?>" start="<?php echo $data["carga"] ?>">
                <?php
                    if($data["estado"]){
                        ?>
                        <div class="d-flex flex-column align-items-stretch h-100">
                            <nav class="navbar navbar-expand-md navbar-light bg-light flex-column">
                                <a href="#" class="navbar-brand">
                                    <img src="image/logo-standalone.png" height="75" alt="CoolBrand" />
                                </a>
                                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                            
                                <div class="collapse navbar-collapse flex-column w-100" id="navbarCollapse">
                                    <div class="navbar-nav flex-column">
                                        <a href="#/" onclick="" class="nav-item nav-link active"><i class="fa fa-home"></i> Inicio</a>
                                        <a href="#/" onclick="" class="nav-item nav-link"><i class="fa fa-money"></i> Caja</a>
                                        <a href="#/" onclick="setCollapse('producto-collapse'); swapClass('#menu-producto','bg-orange-6'); swapClass('#producto-collapse','bg-orange-6')" id="menu-producto" class="nav-item nav-link <?php echo (isset($opcion["producto-collapse"]) && $opcion["producto-collapse"]) ? 'bg-orange-6' : ''; ?>" data-toggle="collapse" data-target="#producto-collapse" aria-controls="producto-collapse" aria-haspopup="true" aria-expanded="<?php echo (isset($opcion["producto-collapse"]) && $opcion["producto-collapse"]) ? 'true' : 'false'; ?>"><i class="fa fa-product-hunt"></i> Productos</a>
                                        <div class="collapse w-100 <?php echo (isset($opcion["producto-collapse"]) && $opcion["producto-collapse"]) ? 'show bg-orange-6' : ''; ?>" id="producto-collapse">
                                            <div class="d-flex flex-column ml-3"> 
                                                <a href="./producto.php" class="nav-item nav-link"><i class="fa fa-clipboard"></i> Actividades</a>
                                                <a href="#/" onclick="productoRegistroFormulario()" class="nav-item nav-link"><i class="fa fa-caret-right"></i> Registrar</a>
                                                <a href="#/" class="nav-item nav-link"><i class="fa fa-list-ol"></i> Inventario</a>
                                                <a href="#/" class="nav-item nav-link"><i class="fa fa-cogs"></i> Administración</a> 
                                            </div>
                                        </div>
                                        <a href="./administracion.php" class="nav-item nav-link" tabIndex="-1"><i class="fa fa-cogs"></i> Administración</a>
                                    </div>
                                    <div class="navbar-nav ml-auto">
                                        <a href="#/" onclick="requestLogout()" class="nav-item nav-link"><i class="fa fa-sign-out"></i> Salir</a>
                                    </div>
                                </div>
                                <Copy />
                            </nav>
                        </div>
                        <div id="menu-<?php echo $data["id"] ?>-process"></div>
                        <?php
                    }else{
                        $mensaje['tipo'] = 'info';
                        $mensaje['cuerpo'] = 'Módulo desactivado.';
                        Alert::mensaje($mensaje);
                    }
                ?>
            </div>
            <script>
                const setCollapse = (collapse) => {
                    var me = $(this);
                    if (me.data('requestRunning')) {
                        return;
                    }
                    me.data('requestRunning', true); 
                    let divProcess = "#menu-<?php echo $data["id"] ?>-process";
                    let divForm = "";
                    $.ajax({
                        type: "POST",
                        url: "./engine/control/componente/menu/set-collapse.php",
                        timeout: 45000,
                        beforeSend: function() {
                            $(divProcess).load("./includes/loading.php");
                            //$(divForm).hide(350);
                            $(divProcess).show(350);
                        },
                        data: {collapse:collapse},
                        complete: function() {
                            me.data('requestRunning', false);
                        },
                        success: function(data) {
                            setTimeout(function() {
                                $(divProcess).html(data);
                            }, 1000);
                        }
                    }).fail(function(jqXHR) {
                        console.log(jqXHR.statusText);
                        me.data('requestRunning', false);
                    });
                } 
            </script>
            <?php
        }
    }
?>