<?php
    class Login{
        public static function me($data){
            if(isset($data) && is_array($data) && count($data) == 4){
                $user = [
                    "email" => $data["login-email"],
                    "pass" => $data["login-pass"]
                ];
                Session::iniciar();
                $_SESSION["usuario"] = Usuario::login($user);
                if(is_object($_SESSION["usuario"])){
                    return true;
                }else{
                    return $_SESSION["usuario"];
                }
            }else{
                return null;
            }
        }
    }
?>