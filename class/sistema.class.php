<?php
    class Sistema{
        private static $alg = "sha512";
        private static $key = "m\$t*rK.yEf3c";

        public static function hashGet($data){
            return Sistema::hashCreate($data);
        }

        private static function hashCreate($data){
            $context = hash_init(Sistema::$alg, HASH_HMAC, Sistema::$key);
            hash_update($context, $data); 
            return hash_final($context);
        }
    }
?>