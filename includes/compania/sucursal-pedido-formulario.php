<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        Compania::sucursalPedidoFormulario();
    }else{
        Sistema::debug('error', 'includes > compania > sucursal-pedido-formulario.php - Usuario no logueado.');
    }
?>