<?php
    require_once 'autoload.class.php';
    if(isset($_POST)){
        Compania::productoHistorial();
    }else{
        Sistema::alerta("Error!", "Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>");
    }
?>