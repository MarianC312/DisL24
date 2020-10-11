<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Sistema::debug('success', 'engine > control > componente > tarea > pendiente > contenido.php - Contenido cargado satisfactoriamente.');
            Componente::usuarioTareasPendientesContent($_POST["data"]);
        }else{
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
            Sistema::debug('error', 'engine > control > componente > tarea > pendiente > contenido.php - Hubo un error al recibir la información del formulario.');
        }
    }else{
        Sistema::debug('error', 'engine > control > componente > tarea > pendiente > contenido.php - Usuario no logueado.');
    }
?>