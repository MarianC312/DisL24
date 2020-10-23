<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            $data = Sistema::dbGetCantidadProductoPorPrefijo($_POST["data"]["prefijo"]);
            if(is_array($data) && count($data) > 0){
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            }
        }else{
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
            Sistema::debug('error', 'engine > producto > cantidad-por-prefijo.php - Error al recibir la información del formulario.');
        }
    }else{
        Sistema::debug('error', 'engine > producto > cantidad-por-prefijo.php - Usuario no logueado.');
    }
?>