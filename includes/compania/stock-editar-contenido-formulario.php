<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
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
            Compania::stockEditarContenidoFormulario($data);
            Sistema::debug("success", "includes > compania > stock-editar-contenido-formulario.php - Información recibida correctamente.");
        }else{
            Sistema::debug("error", "includes > compania > stock-editar-contenido-formulario.php - No se recibió información del formulario.");
        }
    }else{
        Sistema::debug("error", "includes > compania > stock-editar-contenido-formulario.php - Usuario no logueado.");
    }
?>