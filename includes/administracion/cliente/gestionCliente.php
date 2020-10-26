<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Admin::gestionCliente();
            Sistema::debug("success", "includes > administracion > cliente > gestionCliente.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > administracion > cliente > gestionCliente.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > administracion > cliente > gestionCliente.php - Usuario no logueado.");
    }
?>