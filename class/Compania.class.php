<?php
    class Compania{
        public static function stockFormulario(){
            if(Sistema::usuarioLogueado()){
                $data = Compania::stockData();
            }else{
                Sistema::debug('error', 'compania.class.php - stockFormulario - Usuario no logueado.');
            }
        }

        public static function stockData($idCompañia = null, $idSucursal = null){
            
        }

        public static function sucursalGetNombre($idSucursal){
            if(isset($idSucursal) && is_numeric($idSucursal) && $idSucursal > 0){
                $query = DataBase::select("compañia_sucursal", "nombre", "id = '".$idSucursal."'", "");
                if($query){
                    if(DataBase::getNumRows($query) == 1){
                        $dataQuery = DataBase::getArray($query);
                        return $dataQuery["nombre"];
                    }else{
                        return 0;
                    }
                }else{
                    return false;
                }
            }else{
                return null;
            }
        }

        public static function getNombre($idCompañia){
            if(isset($idCompañia) && is_numeric($idCompañia) && $idCompañia > 0){
                $query = DataBase::select("compañia", "nombre", "id = '".$idCompañia."'", "");
                if($query){
                    if(DataBase::getNumRows($query) == 1){
                        $dataQuery = DataBase::getArray($query);
                        return $dataQuery["nombre"];
                    }else{
                        return 0;
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