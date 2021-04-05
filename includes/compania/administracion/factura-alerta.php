<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        Sistema::facturaImpagaAlerta();
    }else{
        Sistema::debug('error', 'includes > administracion > compania > gestionar.php - Usuario no logueado.');
    }
?>  