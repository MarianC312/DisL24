<?php
    require_once 'autoload.class.php';
    if(isset($_POST)){
        Centroayuda::ticketFormulario();
    }else{
        $mensaje['tipo'] = 'danger';
        $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
        Alert::mensaje($mensaje);
    }
?>