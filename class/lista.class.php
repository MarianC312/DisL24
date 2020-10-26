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
        
        public static function compañia($idCompañia = null){
            Session::iniciar();
            $query = DataBase::select("compañia", "*", (($_SESSION["usuario"]->isAdmin()) ? "1" : ((is_numeric($idCompañia)) ? "id = '".$idCompañia."'" : "id = '".$_SESSION["usuario"]->getCompañia()."'")), "ORDER BY nombre ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery;
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
        
        public static function sucursal($idCompañia = null){
            Session::iniciar();
            $query = DataBase::select("compañia_sucursal", "*", "1".((is_numeric($idCompañia)) ? " AND compañia = '".$idCompañia."'" : " AND compañia = '".$_SESSION["usuario"]->getCompañia()."'"), "ORDER BY nombre ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery;
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

        public static function proveedor($idCompañia = null, $idSucursal = null){
            $query = DataBase::select("proveedor", "*", "1".((is_numeric($idCompañia)) ? " AND compañia = '".$idCompañia."'" : "").((is_numeric($idSucursal)) ? " AND sucursal = '".$idSucursal."'" : ""), "ORDER BY nombreFantasia ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery;
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
        
        public static function fabricante(){
            $query = DataBase::select("fabricante", "*", "1", "ORDER BY fabricante ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery["fabricante"];
                    }
                    return $data;
                }else{
                    return 0;
                }
            }else{
                return false;
            }
        } 
        
        public static function productoSubCategoria(){
            $query = DataBase::select("producto_categoria_sub", "*", "1", "ORDER BY subcategoria ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery["subcategoria"];
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

        public static function usuarioRol(){
            $query = DataBase::select("sistema_usuario_rol", "*", "1", "ORDER BY rol ASC");
            if($query){
                if(DataBase::getNumRows($query) > 0){
                    $data = [];
                    while($dataQuery = DataBase::getArray($query)){
                        $data[$dataQuery["id"]] = $dataQuery;
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