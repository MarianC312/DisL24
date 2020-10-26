<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Venta::nuevaFormularioVentas();
            Sistema::debug("success", "includes > ventas > nueva-formulario.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > ventas > nueva-formulario.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes >ventas > registrar-formulario.php - Usuario no logueado.");
    }
?>