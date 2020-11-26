<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Administracion::clienteBuscarFormulario();
            Sistema::debug("success", "includes > administracion > cliente > buscar-formulario.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > administracion > cliente > buscar-formulario.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > administracion > cliente > buscar-formulario.php - Usuario no logueado.");
    }
?>