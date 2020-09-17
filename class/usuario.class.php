<?php
    class Usuario{

        private $id, $nombre, $rol, $email, $estado, $auth = false;

        function __construct($data, $auth){
            $this->id = $data["id"];
            $this->nombre = $data["nombre"];
            $this->rol = $data["rol"];
            $this->email = $data["email"];
            $this->estado = $data["estado"];
            $this->auth = $auth;
        }

        function getAuth(){ return $this->auth; }
        function getId(){ return $this->id; }
        function getNombre(){ return $this->nombre; }
        function getEmail(){ return $this->email; }
        function getEstado(){ return $this->estado; }
        function getRol(){ return $this->rol; }

        function getInfoEstado(){
            $response = [
                "estado" => $this->getEstado()
            ];
            return $response;
        }

        function getInfo($idUsuario = null){
            $rol = Lista::rol();
            $response = [
                "id" => $this->getId(),
                "nombre" => $this->getNombre(),
                "rol" => $rol[$this->getRol()],
                "rolId" => $this->getRol(),
                "email" => $this->getEmail(),
                "estado" => $this->getEstado(),
                "auth" => $this->getAuth()
            ];
            return $response;
        }

        public static function logout(){
            Session::iniciar();
            unset($_SESSION);
            echo '<meta http-equiv="refresh" content="1;URL=./index.php" />';
        }

        public static function login($data){
            $response = null;
            if(isset($data) && is_array($data) && count($data) == 2){
                $response = Usuario::getData($data["email"]);
                if(is_array($response)){
                    return (Sistema::hashGet($data["pass"]) === $response["pass"]) ? New Usuario($response, true) : New Usuario($response, false);
                }else{
                    return $response;
                }
            }else{
                return $response;
            }
        }

        private static function getData($email){
            if(isset($email) && !is_null($email) && strlen($email) >= 7 && $email != ""){
                $query = DataBase::select("usuario", "*", "email = '".$email."' AND estado = 1", "");
                if($query){
                    if(DataBase::getNumRows($query) == 1){
                        $dataQuery = DataBase::getArray($query);
                        foreach($dataQuery AS $key => $value){
                            if(is_int($key)){
                                unset($dataQuery[$key]);
                            }
                        }
                        return $dataQuery;
                    }else{
                        return DataBase::getNumRows($query);
                    }
                }else{
                    return false;
                }
            }else{
                return null;
            } 
        }
    }
?>