<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            foreach($_POST AS $key => $value){
                $data[$key] = $value;
            }
            Session::iniciar();
            $_SESSION["usuario"]->tareaAgregarData($data["tarea"], [$data["input"] => $data["value"]]);
        }else{
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
            Sistema::debug('error', 'tarea-agregar-data.php - Error al recibir la información del formulario.');
        }
    }else{ 
        Sistema::debug('error', 'tarea-agregar-data.php - Usuario no logueado.');
    }
?>