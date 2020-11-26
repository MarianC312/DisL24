<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Admin::usuarioGestionar();
            Sistema::debug("success", "includes > administracion > usuario > usuarioGestionar.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > administracion > usuario > usuarioGestionar.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > administracion > usuario > usuarioGestionar.php - Usuario no logueado.");
    }
?>