<?php
    require_once('autoload.class.php');
    Session::iniciar();
    if($_SESSION["usuario"]->getAuth() && isset($_GET)){
        Usuario::logout();
    }
?>