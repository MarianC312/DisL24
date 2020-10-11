<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            foreach($_POST AS $key => $value){
                if($key != "tarea" && (!isset($value) || is_null($value) || $value == '')){
                    Sistema::debug("alert", "includes > producto > registrar-formulario.php - El parámetro ".$key." tiene un valor incorrecto o inexistente.");
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'Hubo un error con uno de los datos recibidos ['.$key.']. <b>Intente nuevamente o contacte al administrador</b>.';
                    Alert::mensaje($mensaje);
                    exit;
                }
                $data[$key] = $value;
            }
            Producto::registroFormulario($data);
            Sistema::debug("success", "includes > producto > registrar-formulario.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > producto > registrar-formulario.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > producto > registrar-formulario.php - Usuario no logueado.");
    }
?>