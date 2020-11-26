<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Administracion::clienteFacturacionGestion($_POST["idCompania"]);
        }else{
            Sistema::debug('error', 'includes > administracion > cliente > facturacion-gestion.php - Error al recibir la información del formulario.');
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
        }
    }else{
        Sistema::debug('error', 'includes > administracion > cliente > facturacion-gestion.php - Usuario no logueado.');
    }
?>