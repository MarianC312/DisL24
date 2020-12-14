<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Compania::stockRegistroProductoFormulario($_POST["data"]);
        }else{
            Sistema::debug('error', 'includes > compania > stock-registro-producto-formulario.php - Hubo un error al recibir la información del formulario.');
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
        }
    }else{
        Sistema::debug('error', 'includes > compania > stock-registro-producto-formulario.php - Usuario no logueado.');
    }
?>