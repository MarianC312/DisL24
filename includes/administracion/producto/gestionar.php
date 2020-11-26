<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Admin::productoGestionar();
            Sistema::debug("success", "includes > administracion > producto > gestion.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > administracion > producto > gestion.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > administracion > producto > gestion.php - Usuario no logueado.");
    }
?>