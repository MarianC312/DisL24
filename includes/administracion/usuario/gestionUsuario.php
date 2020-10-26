<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Admin::gestionUsuario();
            Sistema::debug("success", "includes > administracion > usuario > gestionUsuario.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > administracion > usuario > gestionUsuario.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > administracion > usuario > gestionUsuario.php - Usuario no logueado.");
    }
?>