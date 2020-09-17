<?php
    require_once('autoload.class.php');
    Session::iniciar();
    if($_SESSION["usuario"]->getAuth() && isset($_GET)){
        header("Access-Control-Allow-Origin: *");
        echo Sistema::json_format($_SESSION["usuario"]->getEstado());
    }
?>