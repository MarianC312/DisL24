<?php
    class Alert{
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
                        if(isset($_GET["debug"]) && $_GET["debug"] == true){
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