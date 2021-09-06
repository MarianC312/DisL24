<?php
    require_once 'autoload.class.php';
    Session::iniciar();
    if($_SESSION["usuario"]->isAdmin()){
        if(isset($_POST)){
            if(is_numeric($_POST["id"]) && $_POST["id"] > 0){
                switch($_POST["id"]){
                    case 1:
                        $force = false;
                        if(($force == "true" && $_SESSION["usuario"]->isAdmin()) || (!isset($_SESSION["lista"]) || !isset($_SESSION["lista"]["producto"]) || !isset($_SESSION["lista"]["producto"]["codificado"]) || !is_array($_SESSION["lista"]["producto"]["codificado"]))){
                            if(($force == "true" && $_SESSION["usuario"]->isAdmin())){
                                Sistema::alerta("Info!", "Force = true && Admin");
                            }
                            if((!isset($_SESSION["lista"]) || !isset($_SESSION["lista"]["producto"]) || !isset($_SESSION["lista"]["producto"]["codificado"]) || !is_array($_SESSION["lista"]["producto"]["codificado"]))){
                                if(!isset($_SESSION["lista"])){
                                    Sistema::alerta("Info!", "Lista no seteada");
                                }
                                if(!isset($_SESSION["lista"]["producto"])){
                                    Sistema::alerta("Info!", "Lista > producto no seteada");
                                }
                                if(!isset($_SESSION["lista"]["producto"]["codificado"])){
                                    Sistema::alerta("Info!", "Lista > producto > codificado no seteada");
                                }
                                if(!is_array($_SESSION["lista"]["producto"]["codificado"])){
                                    Sistema::alerta("Info!", "Lista > producto > codificado no es arreglo seteada");
                                }
                            }
                        }else{
                            Sistema::alerta("Info!", "data base producto codificado update exec.");
                        }
                        break;
                    case 2:

                        break;
                }
            }else{
                Sistema::alerta("Error de identificador!", "El identificador de tarea recibido es nulo o tiene un valor incorrecto.");
            }
        }else{
            Sistema::alerta("Error!", "Ocurrió un error al recibir la información del formulario.");
        }
    }
?>