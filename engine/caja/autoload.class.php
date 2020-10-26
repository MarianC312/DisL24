<?php
    class Autoloader
    {
        public static function register()
        {
            spl_autoload_register(function ($class) {
                $file = "../../class/".strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $class).'.class.php');
                if (file_exists($file)) {
                    require $file;
                    return true;
                }
                return false;
            });
        }
    }
    Autoloader::register();
?>