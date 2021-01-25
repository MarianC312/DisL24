<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){ 
            Cliente::compraLista($_POST["idCliente"], $_POST["small"]);
        }else{
            Sistema::debug("error", "includes > cliente > compra-lista.php - Hubo un error al recibir la información del formulario.");
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información del formulario <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
        }
    }else{
        Sistema::debug("error", "includes > cliente > compra-lista.php - Usuario no logueado.");
    }
?>