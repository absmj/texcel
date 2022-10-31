<?php
require "config.php";
require "app/helpers/functions.php";

spl_autoload_register(function($source){
    if(file_exists(BASEURL . "/app/lib/$source.php"))
        require BASEURL."/app/lib/$source.php";

    if(file_exists(BASEURL . "/app/$source.php"))
        require BASEURL."/app/$source.php";

});
