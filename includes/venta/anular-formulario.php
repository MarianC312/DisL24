<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Venta::anularFormulario($_POST["idVenta"]);
        }else{
            Sistema::debug('error', 'includes > venta > anular-formulario.php - Error al recibir la información del formulario.');
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
        }
    }else{
        Sistema::debug('error', 'includes > venta > anular-formulario.php - Usuario no logueado.');
    }
?>