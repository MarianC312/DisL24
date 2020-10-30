<?php
    class Alert{
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
                            if($_SESSION["usuario"]->debug()){
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