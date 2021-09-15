<?php
    require_once 'autoload.class.php';
    if(isset($_POST)){
        foreach($_POST AS $key => $value){
            if(!isset($value) || is_null($value) || $value == ''){
                $mensaje['tipo'] = 'warning';
                $mensaje['cuerpo'] = 'Hubo un error con uno de los datos recibidos ['.$key.']. <b>Intente nuevamente o contacte al administrador</b>.';
                $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                Alert::mensaje($mensaje);
                exit;
            }
            $data[$key] = $value;
        }
        Centroayuda::ticketComentarioFormularioRegistro($data);
    }else{
        $mensaje['tipo'] = 'danger';
        $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
        Alert::mensaje($mensaje);
    }
?>