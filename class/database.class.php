<?php
    class DataBase{
        private static $data = [
            "host" => "localhost",
            0 => [
                "username" => "root",
                "password" => "",
                "database" => "mine"
            ],
            1 => [
                "username" => "root",
                "password" => "",
                "database" => "u471091307_cayuda"
            ]
        ],
        $link,
        $query;

        private static function dbConnect($idDatabase = 0){
            DataBase::$link = mysqli_connect(DataBase::$data["host"],  DataBase::$data[$idDatabase]["username"], DataBase::$data[$idDatabase]["password"], DataBase::$data[$idDatabase]["database"]);
            if(DataBase::$link){
                mysqli_set_charset(DataBase::$link, "utf8");
                return true;
            }else{
                return false;
            }
        }
        
        private static function close(){
            if(mysqli_close(DataBase::$link)){
                return true;
            }else{
                return false;
            }
        }
        
        public static function update($table, $cols, $claus, $idDatabase = 0){
            if(DataBase::dbConnect($idDatabase)){
                try {
                    DataBase::$query = mysqli_query(DataBase::$link, "UPDATE ".$table." SET ".$cols." WHERE ".$claus);
                } catch (Exception $e) {
                    echo 'ERROR:'.$e->getMessage();
                }
                if(DataBase::$query){
                    return DataBase::$query;
                }
            }
            return false;
        }
        
        public static function insert($table, $cols, $values, $idDatabase = 0){
            if(DataBase::dbConnect($idDatabase)){
                //echo "INSERT INTO ".$table." (".$cols.") VALUES (".$values.")";
                try {
                    DataBase::$query = mysqli_query(DataBase::$link, "INSERT INTO ".$table." (".$cols.") VALUES (".$values.")");
                } catch (Exception $e) {
                    echo 'ERROR:'.$e->getMessage();
                }
                if(DataBase::$query){
                    return true;
                }
            }
            return false;
        }
        
        public static function delete($table, $claus, $idDatabase = 0){
            if(DataBase::dbConnect($idDatabase)){
                try {
                    DataBase::$query = mysqli_query(DataBase::$link, "DELETE FROM ".$table." WHERE ".$claus);
                } catch (Exception $e) {
                    echo 'ERROR:'.$e->getMessage();
                }
                if(DataBase::$query){
                    return DataBase::$query;
                }
            }
            return false;
        }
        
        public static function select($table, $cols, $claus, $ords, $idDatabase = 0){
            if(DataBase::dbConnect($idDatabase)){
                try {
                    //echo "SELECT ".$cols." FROM ".$table." WHERE ".$claus." ".$ords."<br>";
                    DataBase::$query = mysqli_query(DataBase::$link, "SELECT ".$cols." FROM ".$table." WHERE ".$claus." ".$ords);
                } catch (Exception $e) {
                    echo 'ERROR:'.$e->getMessage();
                }
                if(DataBase::$query){
                    return DataBase::$query;
                }
            }
            return false;
        }
        
        public static function getNumRows($query){ return mysqli_num_rows($query); } 
        public static function getArray($query){ return mysqli_fetch_array($query); } 
        public static function dataseek($query,$offset){ return mysqli_data_seek($query, $offset); } 
        public static function getError(){  return mysqli_error(DataBase::getLink()); } 
        private static function getLink(){ return DataBase::$link; } 
        public static function getLastId(){ return mysqli_insert_id(DataBase::getLink()); } 
        public static function getAffectedRows(){ return mysqli_affected_rows(DataBase::getLink()); } 
        public static function getInfo(){ return mysqli_info(DataBase::getLink()); }
    }
?>