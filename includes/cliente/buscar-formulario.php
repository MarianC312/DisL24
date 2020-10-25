<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Cliente::buscarFormulario();
        }else{
            Sistema::debug("error", "includes > cliente > buscar-formulario.php - Hubo un error al recibir la información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > cliente > buscar-formulario.php - Usuario no logueado.");
    }
?>