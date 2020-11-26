<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Administracion::clienteFacturacionFormulario($_POST["idCompania"]);
        }else{
            Sistema::debug('error', 'includes > administracion > cliente > facturacion-formulario.php - Error al recibir la información del formulario.');
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
        }
    }else{
        Sistema::debug('error', 'includes > administracion > cliente > facturacion-formulario.php - Usuario no logueado.');
    }
?>