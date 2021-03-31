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
        $login = Login::me($data);
        //echo '<script>console.log("'.$login.'")</script>';
        if($login){
            Session::iniciar();
            if(isset($_SESSION["usuario"]) && is_object($_SESSION["usuario"])){
                if($_SESSION["usuario"]->getAuth()){
                    $mensaje['tipo'] = 'success';
                    $mensaje['cuerpo'] = 'Bienvenido! Ingresando al sistema...';
                    Alert::mensaje($mensaje);
                    echo '<meta http-equiv="refresh" content="2;URL=./members.php" />';
                }else{
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'El usuario y/o contraseña son incorrectos. <b>Intente nuevamente.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                }
            }else{
                $mensaje['tipo'] = 'info';
                $mensaje['cuerpo'] = 'No se pudo iniciar sesión. Contacte al administrador.';
                $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-info">Regresar</button></div>';
                Alert::mensaje($mensaje);
            }
        }else{
            switch($login){
                case false:
                    $mensaje['tipo'] = 'danger';
                    $mensaje['cuerpo'] = 'Hubo un error al loguearse al sistema. <b>Intente nuevamente o contacte al administrador.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                break;
                case null:
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'Hubo un error con los datos recibidos. <b>Intente nuevamente o contacte al administrador.</b>';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                break;
                case is_numeric($login):
                    $mensaje['tipo'] = 'warning';
                    $mensaje['cuerpo'] = 'Usuario no encontrado o contraseña incorrecta.';
                    $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-warning">Regresar</button></div>';
                    Alert::mensaje($mensaje);
                break;
            }
        }
    }else{
        $mensaje['tipo'] = 'danger';
        $mensaje['cuerpo'] = 'Hubo un error al recibir la información. <b>Intente nuevamente o contacte al administrador.</b>';
        $mensaje['cuerpo'] .= '<div class="d-block p-2"><button onclick="$(\''.$_POST['form'].'\').show(350);$(\''.$_POST['process'].'\').hide(350);" class="btn btn-danger">Regresar</button></div>';
        Alert::mensaje($mensaje);
    }
?>