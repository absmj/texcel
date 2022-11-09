<?php

namespace Controllers;

use Error;
use Exception;
use Processing;

class Texel
{
    public function __construct()
    {
        
    }

    public function index()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        header('Content-Type: application/json;charset=utf-8');
        if(keyValidation(array_keys(INITIAL_STATE), $_POST)){
            $coverImage = $_FILES['coverImage'] ?? null;
            if($_POST["image"]) {
                if(!$coverImage) {
                    echo "File isn't found.";
                    header("Content-Type: plain/text", true, 400);
                    exit;
                }
            }
            
            imageValidation($coverImage);
            sizeValidation();
            textValidation();

            try { 
                $render = Processing\T2P::processing(array_merge($_POST, ['coverImage' => $coverImage['tmp_name'] ?? null])/* state */)->colorize();
                $filename = time().".png";
                imagepng($render->renderedImage, RENDER_PATH . $filename);
                imagedestroy($render->renderedImage);
                echo json_encode( 
                        [
                            'uniqueColors'  => count(array_unique($render->binArray)),
                            'pixelCount'    => count($render->pixelArray),
                            'xPixelSize'    => $render->xPixelSize, 
                            'yPixelSize'    => $render->yPixelSize, 
                            'rendered'      => $filename,
                            'sorting'       => $_POST['sorting'] ?? 0
                        ]
                );
                return;
            } catch (Exception $e) {
                echo $e->message;
                header("Content-Type: text/plain", true, 400);
                // http_response_code(500);
            }

        }

        echo "Bad Request";
        header("Content-Type: text/plain", true, 403);
    }
}