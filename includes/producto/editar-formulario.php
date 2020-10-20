<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Producto::editarFormulario($_POST["idProducto"]);
            Sistema::debug("success", "includes > producto > registrar-formulario.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > producto > registrar-formulario.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > producto > registrar-formulario.php - Usuario no logueado.");
    }
?>