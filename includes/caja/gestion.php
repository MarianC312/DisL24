<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        Caja::gestion();
    }else{
        Sistema::debug('error', 'includes > caja > gestion.php - Usuario no logueado.');
    }
?>