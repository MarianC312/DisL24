<?php
    class Alert{
        public static function feature($id){
            switch($id){
                case 1:
                    Alert::nuevoContenido();
                    break;
                case 2:
                    Alert::companiaStockNuevoContenido();
                    break;

                default:
                    Alert::building();
                    break;
            }
        }

        public static function companiaStockNuevoContenido(){
            $hash = Sistema::hash(rand(0,100));
            Session::iniciar();
            $nombre = $_SESSION["usuario"]->getNombre();
            $nombre = explode(" ", $nombre);
            $nombre = $nombre[0];
            ?>
            <div class="ventana-flotante" id="<?php echo $hash["hash"]; ?>">
                <div class="ventana-container">
                    <div class="ventana-body-container p-0" style="position: relative;">
                        <div class="cg-noticia-container small">
                            <div class="cg-cartel-izquierdo">
                                <div class="mx-5">
                                    <h2>Tenemos muchas novedades</h2>
                                    <p>
                                        Acompañanos en este slide de noticias que preparamos para vos <i class="fa fa-hand-o-right"></i>
                                    </p>
                                </div>
                            </div> 
                            
                            <div class="cg-carousel-container">
                                <div id="carouselExampleIndicators" class="carousel slide" data-interval="false">
                                    <ol class="carousel-indicators">
                                        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                                        <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                                        <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                                        <li data-target="#carouselExampleIndicators" data-slide-to="3"></li>
                                        <li data-target="#carouselExampleIndicators" data-slide-to="4"></li>
                                        <li data-target="#carouselExampleIndicators" data-slide-to="5"></li>
                                    </ol>
                                    <div class="carousel-inner">
                                        <div class="carousel-item active">
                                            <div class="cg-block"></div>
                                            <span class="icon"></span>
                                            <div class="carousel-caption d-none d-md-block">
                                                <h4 class="font-weight-bold"><small>Hola</small> <?php echo mb_strtoupper($nombre) ?></h4>
                                                <p>
                                                    Queremos darte la bienvenida a esta nueva era con EFECE!
                                                </p>
                                                <p>
                                                    A partir de ahora, todo el contenido nuevo te lo informaremos por este medio. Las cosas que necesites saber 
                                                    estarán disponibles por un tiempo determinado cada vez que hagamos una actualización <i class="fa fa-thumbs-up"></i>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="carousel-item">
                                            <div class="cg-block"></div>
                                            <span class="icon"></span>
                                            <div class="carousel-caption d-none d-md-block">
                                                <h4 class="font-weight-bold">Primero lo primero</h4>
                                                <p>
                                                    Mejoramos el sistema de control de stock y productos. Esto significa que vas a poder trabajar
                                                    de manera más ágil y optimizar así tus tareas.
                                                </p>
                                                <p>
                                                    Ahora contas con un formulario dinámico que se genera automáticamente al leer un código de barra.
                                                    Este formulario te mostrará la información que podés editar del producto.
                                                </p>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item"><i class="fa fa-caret-right"></i> Nombre del producto</li>
                                                    <li class="list-group-item"><i class="fa fa-caret-right"></i> Stock disponible</li>
                                                    <li class="list-group-item"><i class="fa fa-caret-right"></i> Precio minorista</li>
                                                    <li class="list-group-item"><i class="fa fa-caret-right"></i> Precio mayorista</li>
                                                    <li class="list-group-item"><i class="fa fa-caret-right"></i> Precio kiosco</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="carousel-item">
                                            <div class="cg-block"></div>
                                            <span class="icon"></span>
                                            <div class="carousel-caption d-none d-md-block">
                                                <h4 class="font-weight-bold">¿Que pasa cuando actualizo el stock?</h4>
                                                <p>
                                                    Una vez que recibís el cartel de "OK" el sistema actualizará tu base de productos y la de tu compañía.<br>
                                                    En este punto cada usuario conectado a las distintas sucursales recibirá esta actualización de
                                                    productos y stock.
                                                </p>
                                                <p>
                                                    Algunas consideraciones <i class="fa fa-hand-o-right"></i>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="carousel-item">
                                            <div class="cg-block"></div>
                                            <span class="icon"></span>
                                            <div class="carousel-caption d-none d-md-block">
                                                <h4 class="font-weight-bold">Al actualizar stock...</h4>
                                                <p>
                                                    Todos los usuarios recibirán la actualización automáticamente y solo verán un cartelito muy discreto
                                                    en el margen inferior derecho.<br>Algo mas o menos así <i class="fa fa-hand-o-down"></i>
                                                </p>
                                                <div class="p-1 my-3">
                                                    <div class="ventana-container small" style="position: initial">
                                                        <div class="ventana-body-container">
                                                            <span class="d-flex justify-content-center align-items-center" style="font-size: 1rem; font-weight: initial; margin-bottom: 0;">
                                                                Actualizando base de productos <span class='ml-2 loader-circle-1' style="font-size: 1rem; font-weight: initial; margin-bottom: 0;"></span>
                                                            </span>
                                                        </div>
                                                    </div> 
                                                </div>
                                                <p>
                                                    Si un usuario está realizando una venta, no se actualizará el stock para no generar discordancia entre los datos
                                                    registrados al momento de realizar una venta.
                                                </p>
                                                <p>
                                                    En su lugar <i class="fa fa-hand-o-right"></i>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="carousel-item">
                                            <div class="cg-block"></div>
                                            <span class="icon"></span>
                                            <div class="carousel-caption d-none d-md-block">
                                                <h4 class="font-weight-bold">...</h4>
                                                <p>
                                                    Habilitamos un botón en el menú "Ventas" el cual tiene la función de actualizar el stock.<br> Se podrá
                                                    actualizar el stock solo si el boton tiene un número de alerta (este será de la cantidad de productos que
                                                    tenga para actualizar)
                                                </p>
                                                <div class="p-1 my-2">
                                                    <div class="d-flex justify-content-around">
                                                        <div>
                                                            <span style="font-size: 1rem; font-weight: initial; margin-bottom: 0;">Sin productos</span>
                                                            <a href="#/" onclick="" class="nav-item nav-link d-flex justify-content-between align-items-center bg-main" style="color: var(--black-1);"><span style="font-size: 1rem; font-weight: initial; margin-bottom: 0;"><i class="fa fa-refresh" aria-hidden="true"></i> Recargar stock</span> <span id="menu-stock-recarga-badge" class="badge badge-pill badge-primary d-none" style="font-size: 1rem; font-weight: initial; margin-bottom: 0;">0</span></a>
                                                        </div>
                                                        <div>
                                                            <span style="font-size: 1rem; font-weight: initial; margin-bottom: 0;">Con productos</span>
                                                            <a href="#/" onclick="" class="nav-item nav-link d-flex justify-content-between align-items-center bg-main" style="color: var(--black-1);"><span style="font-size: 1rem; font-weight: initial; margin-bottom: 0;"><i class="fa fa-refresh" aria-hidden="true"></i> Recargar stock</span> <span id="menu-stock-recarga-badge" class="badge badge-pill badge-primary" style="font-size: 1rem; font-weight: initial; margin-bottom: 0;">7</span></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="carousel-item">
                                            <div class="cg-block"></div>
                                            <span class="icon"></span>
                                            <div class="carousel-caption d-none d-md-block">
                                                <h4 class="font-weight-bold">Consideraciones finales</h4>
                                                <p>
                                                    Si el operador se encuentra realizando una venta, y el sistema detecta que tiene productos
                                                    para actualizar, habilitará la "pelotita" azul con la cantidad de productos para actualizar 
                                                    en el botón de "Recargar Stock".
                                                </p>
                                                <p>
                                                    El operador solo podrá recargar el stock si hay productos para recargar y <u>no está realizando una venta</u>.
                                                </p>
                                                <p>
                                                    Si tenés más dudas consultanos directamente por la mesa de ayuda <i class="fa fa-handshake-o"></i>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="carousel-controls"> 
                                        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="$('#<?php echo $hash['hash'] ?>').remove();" class="btn mn-btn-secondary" style="position: absolute; bottom: 1rem; left: 1rem;">Cerrar</button> 
                    </div>
                </div>
            </div>
            <?php
        }

        public static function nuevoContenido(){
            ?>
            <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel" style="width: 100%; display: flex; margin: 0 auto; background-color: rgba(178, 24, 24, 0.35);">
                <ol class="carousel-indicators">
                    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="3"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="4"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="5"></li>
                </ol>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="image/index-slide/Work-Essentials.png">
                        <div class="carousel-caption d-none d-md-block">
                            <span>MÁS CONTROL</span>
                            <p>A partir de ahora vas a tener más fluidez en las operaciones, vas a encontrar un sistema más claro y con respuestas enfocadas.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="image/index-slide/Team.png">
                        <div class="carousel-caption d-none d-md-block">
                            <span>NUEVO FORMATO Y NOTIFICACIONES</span>
                            <p>Las notificaciones del sistema se dividen en Acciones en proceso, Informacion y Advertencias, y te permitirán tomar decisiones y saber exáctamente que está pasando.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="image/index-slide/Designer.png">
                        <div class="carousel-caption d-none d-md-block">
                            <span>"ACCIONES EN PROCESO"</span>
                            <p>El sistema realizará rutinas de control y administración, los cuales te serán anoticiados mediante este formato de notificación.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="image/index-slide/Development.png">
                        <div class="carousel-caption d-none d-md-block">
                            <span>"INFORMACIÓN"</span>
                            <p>Brindará datos sobre un determinado elemento o aplicación dentro del sistema dentro de este formato.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="image/index-slide/SEO.png">
                        <div class="carousel-caption d-none d-md-block">
                            <span>"ADVERTENCIAS"</span>
                            <p>Las advertencias se darán previas a realizar una acción dentro del sistema la cual requiera tomar una desición antes de ejecutar dicha tarea.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="image/index-slide/Coding.png">
                        <div class="carousel-caption d-none d-md-block">
                            <span>SOPORTE EN LÍNEA</span>
                            <p>Mesa de ayuda para realizar consultas y/o solicitudes llamadas "ticket" los cuales serán tomados y resueltos con la mayor celeridad posible.</p>
                        </div>
                    </div>
                </div>
                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
            <?php
        }

        public static function building(){
            ?>
            <div id="building" class="jumbotron">
                <h1 class="display-3">Hola! <i class="fa fa-hand-paper-o text-primary"></i></h1>
                <p class="lead">
                    Esta sección se encuentra en desarrollo.
                </p>
                <hr class="my-4">
                <p>Pronto tendrás novedades</p>
                <p class="lead">
                    <a class="btn btn-primary btn-lg" onclick="$('#building').remove()" href="#/" role="button">Ok</a>
                </p>
            </div>
            <?php
        }

        public static function mensajeSmall($data){
            switch($data["tipo"])
            {
                case "danger":
                    $titulo = "<i class=\"fa fa-circle\"></i>";
                    break;
                case "warning":
                    $titulo = "<i class=\"fa fa-exclamation-circle\"></i>";
                    break;
                case "success":
                    $titulo = "<i class=\"fa fa-check-circle\"></i>";
                    break;
                case "info":
                    $titulo = "<i class=\"fa fa-info-circle\"></i>";
                    break;
                case "primary":
                    $titulo = "";
                    break;
                case "secondary":
                    $titulo = "";
                    break;
                case "light":
                    $titulo = "";
                    break;
            }
            ?>
            <div class="alert alert-dismissible alert-<?php echo $data["tipo"] ?> text-left">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong><?php echo $titulo ?></strong> 
                <?php echo $data["cuerpo"] ?>
            </div>
            <?php
        }

        public static function mensaje($data)
        {
            if(isset($data))
            {
                $error = error_get_last();
                Session::iniciar();
                switch($data["tipo"])
                {
                    case "danger":
                        $titulo = "<i class=\"fa fa-times\"></i> Error!";
                        break;
                    case "warning":
                        $titulo = "<i class=\"fa fa-info-circle\"></i> Advertencia!";
                        break;
                    case "success":
                        $titulo = "<i class=\"fa fa-check\"></i> Satisfactorio!";
                        break;
                    case "info":
                        $titulo = "<i class=\"fa fa-info\"></i> Información...";
                        break;
                    case "primary":
                        $titulo = "";
                        break;
                    case "secondary":
                        $titulo = "";
                        break;
                    case "light":
                        $titulo = "";
                        break;
                }
                ?>
                <div class="alert alert-dismissible alert-<?php echo $data["tipo"] ?> text-left">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <h4 class="alert-heading"><?php echo $titulo ?></h4>
                    <p>
                        <?php echo $data["cuerpo"] ?>
                    </p>
                    <div class="p-2 text-left">
                        <?php 
                            if($_SESSION["usuario"]->isAdmin()){
                                ?>
                                <span class="font-weight-bold">Información para el administrador:</span>
                                <div class="dropdown-divider border-<?php echo $data["tipo"] ?>"></div>
                                <div class="p-2">
                                    <?php
                                        if(is_array($error) && count($error) > 0){
                                            ?>
                                            <b>Tipo:</b> <?php echo $error["type"] ?>.<br>
                                            <b>Mensaje:</b> <?php echo $error["message"] ?>.<br>
                                            <b>Archivo:</b> <?php echo $error["file"] ?>.<br>
                                            <b>Línea:</b> <?php echo $error["line"] ?>.<br>
                                            <?php
                                        }
                                    ?>
                                    <b>DB:</b> <?php echo DataBase::getError() ?>.<br>
                                </div>
                                <?php
                            }
                        ?>
                    </div>
                </div>
                <?php
            }
        }
    }
?>