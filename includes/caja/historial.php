<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Caja::historial($_POST["idCaja"], 2, $_POST["small"]);
        }else{
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
            Sistema::debug('error', 'includes > caja > historial.php - Error al recibir la información del formulario.');
        }
    }else{
        Sistema::debug('error', 'includes > caja > historial.php - Usuario no logueado.');
    }
?>