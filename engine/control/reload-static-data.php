<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Sistema::reloadStaticData();
        }else{
            Sistema::debug('error', 'engine > control > reload-static-data.php - Error al recibir la información del formulario.');
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
        }
    }else{
        Sistema::debug('error', 'engine > control > reload-static-data.php - Usuario no logueado.');
    }
?>