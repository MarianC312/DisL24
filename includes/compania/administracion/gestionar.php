<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        Compania::configurar();
    }else{
        Sistema::debug('error', 'includes > administracion > compania > gestionar.php - Usuario no logueado.');
    }
?>  