<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Componente::usuarioTareasPendientes();
        }else{
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
            Sistema::debug('error', 'includes > componente > usuario-tareas-pendientes.php - Hubo un error al recibir la información del formulario.');
        }
    }else{
        Sistema::debug('error', 'includes > componente > usuario-tareas-pendientes.php - Usuario no logueado.');
    }
?>