<?php
    require_once 'autoload.class.php';
    if(isset($_POST)){
        Centroayuda::ticketComentarioRecarga($_POST["idTicket"], $_POST["fecha"]);
    }else{
        $mensaje['tipo'] = 'danger';
        $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
        Alert::mensaje($mensaje);
    }
?>