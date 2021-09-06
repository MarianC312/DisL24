<?php
    class Alert{
        public static function feature($id){
            switch($id){
                case 1:
                    Alert::nuevoContenido();
                    break;

                default:
                    Alert::building();
                    break;
            }
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