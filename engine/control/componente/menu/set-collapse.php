<?php
    require_once 'autoload.class.php';
    Session::iniciar();
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            if(isset($_POST["collapse"]) && strlen($_POST["collapse"]) > 0){
                if(isset($_SESSION["componente"]["menu"]["opcion"][$_POST["collapse"]])){
                    $_SESSION["componente"]["menu"]["opcion"][$_POST["collapse"]] = !$_SESSION["componente"]["menu"]["opcion"][$_POST["collapse"]];
                    Sistema::debug("success","set-collapse.php - Componente ".$_POST["collapse"]." actualizado.");
                }else{
                    $_SESSION["componente"]["menu"]["opcion"][$_POST["collapse"]] = true;
                    Sistema::debug("success","set-collapse.php - Componente ".$_POST["collapse"]." creado.");
                }
            }else{
                Sistema::debug("error", "set-collapse.php - Data no seteada.");
            }
        }else{
            Sistema::debug("error","set-collapse.php - No se recibió información.");
        }
    }else{
        Sistema::debug("error","set-collapse.php - No autorizado.");
    }
?> 