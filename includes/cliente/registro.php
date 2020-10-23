<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Cliente::registroFormulario();
            Sistema::debug("success", "includes > cliente > registro-formulario.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > cliente > registro-formulario.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > cliente > registro-formulario.php - Usuario no logueado.");
    }
?>