<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){ 
        if(isset($_GET)){
            header("Access-Control-Allow-Origin: *");
            echo Sistema::json_format(Compania::stockData($_GET["c"], $_GET["s"]));
        }else{
            Sistema::debug('error', 'engine > control > compania > stock-data.php - Error al recibir la información del formulario.');
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
        }
    }else{
        Sistema::debug('error', 'engine > control > compania > stock-data.php - Usuario no logueado.');
    }
?>