<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Producto::registroFormulario((isset($_POST["corroborar"])) ? $_POST["corroborar"] : true, isset($_POST["codigo"]) ? $_POST["codigo"] : 0);
            Sistema::debug("success", "registrar-formulario.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "registrar-formulario.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "registrar-formulario.php - Usuario no logueado.");
    }
?>