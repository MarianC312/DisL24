<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Cliente::registroFormulario();
        }else{
            Sistema::debug("error", "includes > cliente > registro-formulario.php - Hubo un error al recibir la información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > cliente > registro-formulario.php - Usuario no logueado.");
    }
?>