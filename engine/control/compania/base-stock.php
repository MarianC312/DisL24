<?php
    require_once 'autoload.class.php';
    $response = [
        "status" => false,
        "mensajeUser" => "",
        "mensajeAdmin" => "",
        "dependencia" => "engine / control / compania / stock",
        "data" => [
            "array" => null,
            "count" => null,
            "type" => null
        ]
    ];
    if(Sistema::usuarioLogueado()){ 
        Session::iniciar();
        if((isset($_POST) && isset($_POST["force"]) && $_POST["force"] == "true" && $_SESSION["usuario"]->isAdmin()) || $_SESSION["usuario"]->shouldReloadStaticData()){
            $response["status"] = true;
            $response["data"]["array"] = $_SESSION["lista"]["compañia"][$_SESSION["usuario"]->getCompañia()]["sucursal"]["stock"];
            echo Sistema::json_format($response);
        }else{
            $response["mensajeUser"] = "No es necesario cargar la base de stock de productos.";
            echo Sistema::json_format($response);
        }
    }else{
        Sistema::debug('error', 'engine / control / compania / stock - Usuario no logueado.'); 
        $response["mensajeUser"] = 'Debés estar logueado para ingresar a esta información.';
        echo Sistema::json_format($response);
        exit;
    }
?>