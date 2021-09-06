<?php
    require_once 'autoload.class.php';
    $response = [
        "status" => false,
        "mensajeUser" => "",
        "mensajeAdmin" => "",
        "dependencia" => "engine / control / compania / base-producto",
        "data" => [
            "array" => null,
            "count" => null,
            "type" => null
        ]
    ];
    if(Sistema::usuarioLogueado()){ 
        Session::iniciar();
        if(isset($_POST)){
            if((isset($_POST["chunk"]) && $_POST["chunk"] > 0) || (isset($_POST["force"]) && $_POST["force"] == "true" && $_SESSION["usuario"]->isAdmin()) || $_SESSION["usuario"]->shouldReloadFEStaticData()){
                $response["status"] = true;
                $response["data"]["array"] = Producto::FEChunkLoad($_POST["chunk"], $_POST["force"]);
                $_SESSION["usuario"]->setLastReloadFEStaticData();
                echo Sistema::json_format($response);
            }else{
                $response["mensajeUser"] = "No es necesario cargar la base de productos.";
                echo Sistema::json_format($response);
            }
        }else{
            $response["mensajeUser"] = "Ocurrió un error al recibir los parámetros de la solicitud.";
            echo Sistema::json_format($response);
        }
    }else{
        Sistema::debug('error', 'engine / control / compania / base-producto - Usuario no logueado.'); 
        $response["mensajeUser"] = 'Debés estar logueado para ingresar a esta información.';
        echo Sistema::json_format($response);
        exit;
    }
?>