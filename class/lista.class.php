<?php
    class Lista{
        public static function productoTipo(){
            $query = DataBase::select("producto_tipo", "*", "1", "ORDER BY tipo ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery["tipo"];
                    }
                    return $data;
                }else{
                    return 0;
                }
            }else{
                return false;
            }
        } 
        
        public static function productoCategoria(){
            $query = DataBase::select("producto_categoria", "*", "1", "ORDER BY categoria ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery["categoria"];
                    }
                    return $data;
                }else{
                    return 0;
                }
            }else{
                return false;
            }
        }

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

        public static function compañia(){
            $query = DataBase::select("compañia", "*", "1", "ORDER BY nombre ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[] = $dataQuery;
                    }
                    foreach($data AS $key => $value){
                        foreach($value AS $iKey => $iValue){
                            if(is_int($iKey)){
                                unset($data[$key][$iKey]);
                            }
                        }
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