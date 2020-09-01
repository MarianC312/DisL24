<?php
    class Session{
        public static function iniciar()
        {
            if ( Session::estado() === FALSE ) session_start();
        }
        
        public static function estado()
        {
            if ( php_sapi_name() !== 'cli' ) {
                if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                    return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
                } else {
                    return session_id() === '' ? FALSE : TRUE;
                }
            }
            return FALSE;
        }
    }
?>