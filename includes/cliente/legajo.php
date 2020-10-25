<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Cliente::legajo($_POST["idCliente"]);
        }else{
            Sistema::debug("error", "includes > cliente > legajo.php - Error al recibir la información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > cliente > legajo.php - Usuario no logueado.");
    }
?>