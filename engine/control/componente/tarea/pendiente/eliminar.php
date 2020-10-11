<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Sistema::debug('success', 'engine > control > componente > tarea > pendiente > eliminar.php - Acceso satisfactorio, procediendo a eliminar la tarea.');
            Session::iniciar();
            if(isset($_SESSION["tarea"][$_POST["tarea"]])){
                unset($_SESSION["tarea"][$_POST["tarea"]]);
                echo '<script>tareasPendientesLoadHeader(true);</script>';
                Sistema::debug('success', 'engine > control > componente > tarea > pendiente > eliminar.php - Tarea eliminada satisfactoriamente.');
            }else{
                Sistema::debug('error', 'engine > control > componente > tarea > pendiente > eliminar.php - La tarea no existe.');
            }
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