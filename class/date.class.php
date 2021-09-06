<?php
    class Date{
        public static function current(){
            // Set Timezone
            date_default_timezone_set( 'America/Buenos_aires' );

            return date("Y-m-d H:i:s");
        }

        public static function minus($min){
            // Set Timezone
            date_default_timezone_set( 'America/Buenos_aires' );

            return date("Y-m-d H:i:s", strtotime("-".$min." minutes", strtotime("Y-m-d H:i:s")));
        }
    }
?>