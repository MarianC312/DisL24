<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Admin::clienteGestionar();
            Sistema::debug("success", "includes > administracion > cliente > gestionar.php - InformaciÃ³n recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > administracion > cliente > gestionar.php - No se recibiÃ³ informaciÃ³n del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > administracion > cliente > gestionar.php - Usuario no logueado.");
    }
?>