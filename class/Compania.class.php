<?php
    class Compania{
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