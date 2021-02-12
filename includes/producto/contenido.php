<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Producto::contenido($_POST["idProducto"], $_POST["tipo"], $_POST["productoTipo"]);
        }else{
            Sistema::debug("error", "includes > compañia > stock-contenido.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > compañia > stock-contenido.php - Usuario no logueado.");
    }
?>