<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            Sistema::debug('success', 'componente > tarea > pendiente > container > set-view.php - Información del formulario recibida.');
            Session::iniciar();
            if(isset($_SESSION["componente"]["tarea"]["pendiente"]["container"])){
                $_SESSION["componente"]["tarea"]["pendiente"]["container"] = !$_SESSION["componente"]["tarea"]["pendiente"]["container"];
                Sistema::debug("success","componente > tarea > pendiente > container > set-view.php - Componente tarea pendiente container actualizado.");
            }else{
                $_SESSION["componente"]["tarea"]["pendiente"]["container"] = true;
                Sistema::debug("success","componente > tarea > pendiente > container > set-view.php - Componente tarea pendiente container creado.");
            }
        }else{
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
            Sistema::debug('error', 'componente > tarea > pendiente > container > set-view.php - Error al recibir la información del formulario.');
        }
    }else{
        Sistema::debug('error', 'componente > tarea > pendiente > container > set-view.php - Usuario no logueado.');
    }
?>