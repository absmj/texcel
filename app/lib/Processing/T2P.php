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
        foreach(str_split($this->bin2Hex(), 6) as $color)
            $this->pixelArray[] = Math::hexToRgb($color);

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
        
        return $this;
    }

}