<?php
    class Lista{
        public static function rol(){
            $query = DataBase::select("sistema_usuario_rol", "*", "1", "ORDER BY rol ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery["rol"];
                    }
                    return $data;
                }else{
                    return 0;
                }
            }else{
                return false;
            }
        }
    }
?>