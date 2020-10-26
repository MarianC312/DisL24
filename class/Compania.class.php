<?php
    class Compania{
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

        public static function getCompania($idCompañia = null){ 
            if(Sistema::usuarioLogueado()){
                $data = Lista::compañia($idCompañia);
                if(is_array($data) && count($data) > 0){
                    return $data;
                }else{
                    Sistema::debug('error', 'compania.class.php - getCompania - Error al recibir la información de la lista de compañía.');
                }
            }else{
                Sistema::debug('error', 'compania.class.php - getCompania - Usuario no logueado.');
            }
            return false;
        } 

        public static function buscarFormulario()
        {
            $data = Compania::getCompania(); 
            ?>
            <div> 
                <div class="titulo">Búsqueda de Compañía</div>
                <div id="compania-buscar-process"></div>
                <form id="compania-buscar-form" action="./includes/compañia/buscar.php" form="#compania-buscar-form" process="#compania-buscar-process"> 
                    <div class="form-group">
                        <label for="compania"><i class="fa fa-list-alt"></i> Seleccione Compañía</label>
                        <select class="form-control" id="compania" name="compania">
                            <option value=""> -- </option>
                            <?php
                                foreach($data AS $key => $value){
                                    echo '<option value="'.$key.'">'.$value["nombre"].'</option>';
                                }
                            ?>
                        </select>
                        <button type="button" onclick="buscarCompañiaFormulario()" class="btn btn-success">Buscar</button>
                    </div>
                </form>
            </div>  
            <?php 
        }
    }
?>