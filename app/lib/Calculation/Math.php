<?php

namespace Calculation;

class Math
{
    private function __construct()
    {
        
    }

    public static function area($w, $h)
    {
        return $w * $h;
    }

    public static function perimeter($w, $h)
    {
        return 2 * ($w + $h);
    }

    public static function calcGCP(array $nums)
    {
        $gcd = function ($a, $b) use (&$gcd) { return $b ? $gcd($b, $a % $b) : $a; };
        return array_reduce($nums, function($a, $b) use ($gcd){
            return $gcd($a, $b);
        });
    }

    public static function optimalRectangleSize($width, $height, $ratio, $count)
    {
        $x = function($row) use (&$x, $count, $height, $width, $ratio) {
            $y = ceil($count / $row);

            $rectHeight = ($height - $row - 1) / $row;
            $rectWidth = $rectHeight * $ratio;
            
            if($y * $rectWidth + ($y - 1) > $width) {
                $x($row + 1);
            }

            return floor($rectWidth);
        };

        $y = function($column) use (&$y, $count, $height, $width, $ratio) {
            $x = ceil($count / $column);

            $rectWidth = ($width - $column - 1) / $column;
            $rectHeight = $rectWidth / $ratio;
            
            if($x * $rectHeight + ($x - 1) > $height) {
                $y($column + 1);
            }

            return floor($rectHeight);
        };

        $iteration = (2 * ($width + $height)) / $count;
        $xPosition = $x(($iteration));
        $yPosition = $y(($iteration));

        return [$xPosition, $yPosition];
    }

    public static function findAspectRatio($width, $height)
    {
        return $width / self::calcGCP([$width, $height]) / ($height / self::calcGCP([$width, $height]));
    }

    public static function hexToRgb($hex, $alpha = false)
    {
        
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : $hex));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : $hex));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : $hex));
        if ( $alpha ) {
           $rgb['a'] = $alpha;
        }
        return $rgb;
    }
}