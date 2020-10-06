<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        Sistema::reloadStaticData();
        Sistema::debug("success","header-usuario-alerta.php - Revisión satisfactoria identificador ".$_POST["id"].".");
        Componente::headerUsuarioAlert($_POST["id"], null, (($_POST["id"] == 1) ? "bell" : "envelope"));
    }else{
        Sistema::debug("error","header-usuario-alerta.php - Usuario no logueado.");
    }
?>