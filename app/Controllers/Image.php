<?php

namespace Controllers;

class Image
{
    public function __construct()
    {

    }

    public function index()
    {
        if($_GET['rendered'] ?? false){
            $filename = RENDER_PATH . $_GET['rendered'];
            
            if(!file_exists($filename)) {
                show404();
            }
        
            $image = imagecreatefrompng($filename);
            
            header("Content-Type: image/png");
            imagepng($image);
            imagedestroy($image);
            // unlink($filename);
            return;
        }

        // show404();
    }
}