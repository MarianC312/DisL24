<!DOCTYPE html>
<?php
    require_once 'autoload.class.php';
    if(!Sistema::usuarioLogueado()){
        echo '<meta http-equiv="refresh" content="0;URL=./index.php" />';
        exit;
    }
?>
<html lang="en">

<head>
    <?php require_once 'header.php' ?>
</head> 

<body>
    <div id="main-body" class="d-flex">
        <div id="left-content" class="d-flex">
            <?php Componente::menu(); ?>
        </div>
        <div id="right-content" class="flex-grow-1">
                <?php Componente::headerUsuario() ?>
                <div id="right-content-data">
                    <?php
                        Session::iniciar();
                        echo '<pre>';
                        print_r($_SESSION["tarea"]);
                        echo '</pre>';
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>