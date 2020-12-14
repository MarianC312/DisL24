<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            if($_POST["codificado"] === "false") $_POST["exceptions"] .= ",codigo";
            $excepciones = explode(",", ((isset($_POST["exceptions"])) ? $_POST["exceptions"] : ""));
            foreach($_POST AS $key => $value){ 
                $distinto = true;
                foreach($excepciones AS $iKey => $iValue){
                    if($iValue == $key){
                        $distinto = false;
                        break;
                    }
                }
                if($distinto && (!isset($value) || is_null($value) || $value == '')){
                    Sistema::debug('Alert', 'engine > producto > registro.php - Hubo un error con el valor del input ['.$key.'].');
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'Hubo un error con uno de los datos recibidos ['.$key.']. <b>Intente nuevamente o contacte al administrador</b>.';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                    exit;
                }
                $data[$key] = $value;
            }
            if($data["codificado"] === $_POST["codificado"]){
                if($data["codificado"] === "true"){
                    Producto::registro($data);
                }elseif($data["codificado"] === "false"){
                    Producto::nocodifRegistro($data);
                }else{
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Error en la codificación del producto. <b>Contacte al administrador a la brevedad.</b>';
                    Alert::mensaje($mensaje);
                    Sistema::debug('Error', 'engine > producto > registro.php - Error en codificación. Ref.: '.$data["codificado"]);
                }
            }else{
                $mensaje['tipo'] = 'danger';
                $mensaje['cuerpo'] = 'Hubo un error al comprobar la codificación del producto. <b>Intente nuevamente o contacte al administrador.</b>';
                Alert::mensaje($mensaje);
                Sistema::debug('Error', 'engine > producto > registro.php - Error en comprobación de codificación.');
            }
        }else{
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
            Sistema::debug('Error', 'engine > producto > registro.php - Hubo un error al recibir la información del formulario.');
        }
    }else{
        Sistema::debug('Error', 'engine > producto > registro.php - Usuario no logueado.');
    }
?>