<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Admin::visualizarUsuario($_POST["compania"]);
            Sistema::debug("success", "includes > cliente > registro-formulario.php - InformaciÃ³n recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > cliente > registro-formulario.php - No se recibiÃ³ informaciÃ³n del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > cliente > registro-formulario.php - Usuario no logueado.");
    }
?>