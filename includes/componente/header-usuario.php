<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        Sistema::reloadStaticData();
        Componente::headerUsuarioMain($_SESSION["usuario"]->getInfo());
    }else{
        Sistema::debug("error","Error: header-usuario.php - Usuario no logueado.");
    }
?>