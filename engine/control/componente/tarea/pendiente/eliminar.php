<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Sistema::debug('success', 'engine > control > componente > tarea > pendiente > eliminar.php - Acceso satisfactorio, procediendo a eliminar la tarea.');
            Session::iniciar();
            $_SESSION["usuario"]->tareaEliminar($_POST["tarea"]);
        }else{
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
            Sistema::debug('error', 'engine > control > componente > tarea > pendiente > eliminar.php - Error al recibir la información del formulario.');
        }
    }else{
        Sistema::debug('error', 'engine > control > componente > tarea > pendiente > eliminar.php - Usuario no logueado.');
    }
?>