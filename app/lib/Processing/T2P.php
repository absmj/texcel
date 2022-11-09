<?php

namespace Processing;

use Calculation\Math;
use Exception;

class T2P
{

    public $state;
    public $renderedImage;
    public $xPixelSize;
    public $yPixelSize;
    public $pixelArray;
    public $binnedText;
    public $binArray;
    public $image;

    private function __construct($state)
    {
        $this->state = (object)$state;
    }

    public static function processing($state) : T2P
    {
        return new self($state ?? INITIAL_STATE);
    }

    private function calcEveryPixelSize($h = null) : T2P
    {
        $countPixelArray =  count($this->pixelArray);

        $c = $this->state->width / ($h ?? $this->state->height);

        $h = ceil(sqrt(Math::area($this->state->width, $this->state->height) / ($countPixelArray * $c)));
        $this->xPixelSize = $this->state->width / ceil($this->state->width / ($c * $h));
        $this->yPixelSize = $this->state->height / ceil($this->state->height / $h);

        // if($this->xPixelSize < 1 || $this->xPixelSize < 1){
        //     if($this->xPixelSize < 1) $this->state->width *= $h;
        //     if($this->xPixelSize < 1) $this->state->height *= $h;
        //     $this->calcEveryPixelSize();
        // }
        
        // print_r($countPixelArray); die;
        return $this;

    }


    private function correctionText() : string
    {
        return trim(
            addslashes($this->state->text)
        );
    }

    private function bin2Hex() : string
    {
        $this->binnedText = bin2hex($this->correctionText());
        return $this->binnedText;
    }

    private function generatePixelColor() : T2P
    {
        $this->binArray = str_split($this->bin2Hex(), 6);
        foreach($this->binArray as $color)
            $this->pixelArray[] = Math::hexToRgb($color);

        
        if($this->state->optional['sorting']) {
            switch($this->state->optional['sorting']) {
                case SORTING['A-Z']:
                    uasort($this->pixelArray, function($a, $b) {
                        $a = Math::rgbToHsl($a);
                        $b = Math::rgbToHsl($b);
                        // dd($a);
                        if(!Math::hueAreInSameInterval($a['h'], $b['h'])) {
                            if($a['h'] < $b['h']) return -1;
                            if($a['h'] > $b['h']) return 1;
                        }
        
                        if($a['l'] < $b['l']) return 1;
                        if($a['l'] > $b['l']) return -1;
                        if($a['s'] < $b['s']) return -1;
                        if($a['s'] > $b['s']) return 1;
        
                    });
                    break;

                case SORTING['Z-A']:
                    uasort($this->pixelArray, function($a, $b) {
                        $a = Math::rgbToHsl($a);
                        $b = Math::rgbToHsl($b);
        
                        if(!Math::hueAreInSameInterval($a['h'], $b['h'])) {
                            if($a['h'] < $b['h']) return 1;
                            if($a['h'] > $b['h']) return -1;
                        }
        
                        if($a['l'] < $b['l']) return -1;
                        if($a['l'] > $b['l']) return 1;
                        if($a['s'] < $b['s']) return 1;
                        if($a['s'] > $b['s']) return -1;
        
                    });
                    break;
            }
            $this->pixelArray = array_values($this->pixelArray);   
        }

        return $this;
    }

    private function createImage() 
    {
        $this->generatePixelColor()->calcEveryPixelSize();
        return imagecreatetruecolor($this->state->width, $this->state->height);
    }

    private function fillRectangle($x, $y, $color)
    {
        imagefilledrectangle($this->renderedImage, $x, $y, $x + $this->xPixelSize, $y + $this->yPixelSize, $color);
        return $this;
    }

    private function fillEllipse($x, $y, $color)
    {
        imagefilledellipse($this->renderedImage, $x + ($this->xPixelSize / 2), $y + ($this->yPixelSize / 2), $this->xPixelSize, $this->yPixelSize, $color);
        return $this;
    }

    private function fillImage()
    {
        if($this->state->image) {
            $this->image = imagecreatefromjpeg($this->state->coverImage);
        }

        return $this;
    }

    private function iterateImage($x, $y)
    {
        $this->image = imagescale($this->image, $this->xPixelSize, $this->yPixelSize);
        imagecopymerge($this->renderedImage,$this->image,$x,$y,0,0,$this->state->width,$this->state->height,30);
    }

    private function locateImage()
    {
        $this->image = imagescale($this->image, $this->state->width, $this->state->height);
        imagecopymerge($this->renderedImage,$this->image,0,0,0,0,$this->state->width,$this->state->height,30);
    }

    private function shape($x, $y, $color)
    {
        switch($this->state->shape)
        {
            case SHAPE['RECTANGLE']:
                return $this->fillRectangle($x, $y, $color);
            
            case SHAPE['ELLIPSE']:
                return $this->fillEllipse($x, $y, $color);
            
            default:
                throw new Exception("Shape type doesnot supported yet.");
            
        }
    }

    private function blur()
    {
        if(!(int)$this->state->optional['blur']) return;
        
        $blur = 100 - $this->state->optional['blur'];

        $s1x = Math::percent($this->state->width, $blur);
        $s1y = Math::percent($this->state->height, $blur);
        $s_img1 = imagecreatetruecolor($s1x,$s1y);
        imagecopyresampled($s_img1, $this->renderedImage, 0, 0, 0, 0, $s1x, $s1y, $this->state->width, $this->state->height);
        imagefilter($s_img1, IMG_FILTER_GAUSSIAN_BLUR);
      
        $s2x = Math::percent($s1x, 200);
        $s2y = Math::percent($s1y, 200);
        $s_img2 = imagecreatetruecolor($s2x,$s2y);
        imagecopyresampled($s_img2, $s_img1, 0, 0, 0, 0, $s2x, $s2y, $s1x, $s1y);
        imagedestroy($s_img1);
        imagefilter($s_img2, IMG_FILTER_GAUSSIAN_BLUR);
      
        imagecopyresampled($this->renderedImage, $s_img2, 0, 0, 0, 0, $this->state->width, $this->state->height, $s2x, $s2y);
        imagedestroy($s_img2);
        imagefilter($this->renderedImage, IMG_FILTER_GAUSSIAN_BLUR);
        return $this;
    }

    public function colorize()
    {
        $i = 0;        

        $this->renderedImage = $this->createImage();
        imagealphablending($this->renderedImage, true);
        
        if($this->state->image){
            $this->fillImage();
        }
            

        for($y = 0; $y < $this->state->height; $y+=$this->yPixelSize){ 
            for($x = 0; $x < $this->state->width; $x+=$this->xPixelSize){
                $pixel = $this->pixelArray[$i];
                $color = imagecolorallocate($this->renderedImage, $pixel['r'], $pixel['g'], $pixel['b']);
                
                if($this->state->image && $this->state->iterations)
                    $this->shape($x, $y, $color)->iterateImage($x, $y);
                else
                    $this->shape($x, $y, $color);

                if($i < count($this->pixelArray) - 1) $i++;
                else if($this->state->repeating) {      
                    $i=0;
                    continue;
                }
                else break;
                
            }
        }
        
        

        if($this->state->image && !$this->state->iterations)
            $this->locateImage();
        

            $gaussian = array(array(8.0, 16.0, 8.0), array(32.0, 64.0, 32.0), array(8.0, 16.0, 8.0));
            imageconvolution($this->renderedImage, $gaussian, 256, 0);

        $this->blur();
        return $this;
    }

}