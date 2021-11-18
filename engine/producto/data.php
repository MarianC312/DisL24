<?php
    require_once 'autoload.class.php'; 
    $response = [
        "status" => false,
        "mensajeUser" => "",
        "mensajeAdmin" => "",
        "dependencia" => "engine / producto > data",
        "data" => [
            "array" => []
        ]
    ];
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            $response["status"] = true;
            $response["data"]["array"] = Sistema::productoData($_POST["idProducto"], $_POST["tipo"]);
        }else{
            $response["mensajeUser"] = "Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>";
        }
    }else{
        $response["mensajeUser"] = "Debes estar logueado para acceder a esta información.";
    }
    echo Sistema::json_format($response);
?>