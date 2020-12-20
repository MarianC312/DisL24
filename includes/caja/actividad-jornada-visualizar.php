<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Caja::actividadJornadaVisualizar($_POST["idJornada"]);
        }else{
            Sistema::debug('error', 'includes > caja > actividad-jornada-visualizar.php - Error al recibir la información del formulario.');
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
        }
    }else{
        Sistema::debug('error', 'includes > caja > actividad-jornada-visualizar.php - Usuario no logueado.');
    }
?>