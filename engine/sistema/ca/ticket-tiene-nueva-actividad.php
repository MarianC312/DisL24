<?php
    require_once 'autoload.class.php';
    if(isset($_POST)){
        echo Sistema::json_format(Centroayuda::ticketTieneNuevaActividad());
    }
?>