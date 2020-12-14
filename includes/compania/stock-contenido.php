<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Compania::stockContenido($_POST["idProducto"], $_POST["tipo"], $_POST["productoTipo"]);
            Sistema::debug("success", "includes > compañia > stock-contenido.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > compañia > stock-contenido.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > compañia > stock-contenido.php - Usuario no logueado.");
    }
?>