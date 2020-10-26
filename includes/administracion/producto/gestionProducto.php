<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Admin::gestionProducto();
            Sistema::debug("success", "includes > administracion > producto > gestionProducto.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > administracion > producto > gestionProducto.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > administracion > producto > gestionProducto.php - Usuario no logueado.");
    }
?>