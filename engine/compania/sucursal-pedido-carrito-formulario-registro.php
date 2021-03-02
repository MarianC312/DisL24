<?php
    require_once 'autoload.class.php';
    if(Sistema::usuarioLogueado()){
        if(isset($_POST)){
            $excepciones = (strlen($_POST["exceptions"]) > 0) ? explode(",", ((isset($_POST["exceptions"])) ? $_POST["exceptions"] : "")) : [];
            foreach($_POST AS $key => $value){ 
                $distinto = true;
                foreach($excepciones AS $iKey => $iValue){
                    if($iValue == $key){
                        $distinto = false;
                        break;
                    }
                }
                if($distinto && (!isset($value) || is_null($value) || $value == '')){
                    Sistema::debug('Alert', 'engine > compania > sucursal-pedido-carrito-formulario-registro.php - Hubo un error con el valor del input ['.$key.'].'); 
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'Hubo un error con uno de los datos recibidos ['.$key.']. <b>Intente nuevamente o contacte al administrador</b>.';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                    exit;
                }
                $data[$key] = $value;
            }
            Compania::sucursalPedidoCarritoFormularioRegistrar($data);
        }else{
            Sistema::debug('Error', 'engine > compania > sucursal-pedido-carrito-formulario-registro.php - Hubo un error al recibir la información del formulario.');
            $mensaje['tipo'] = 'danger';
            $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
            Alert::mensaje($mensaje);
        }
    }else{
        Sistema::debug('Error', 'engine > compania > sucursal-pedido-carrito-formulario-registro.php - Usuario no logueado.');
    }
?>