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

    public static function rgbToHsl($rgb)
    {
        list($r, $g, $b) = [$rgb['r'], $rgb['g'], $rgb['b']];

        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $h = 0;
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if($d == 0) {
            $h = $s = 0;
        } else {
            $s = $d / (1 - abs(2 * $l - 1));

            switch ($max) {
                case $r:
                    $h = 60 * fmod((($g - $b) / $d), 6);
                    if($b > $g) $h += 360;
                    break;

                case $g:
                    $h = 60 * (($g - $r) / $d + 2);
                    break;

                case $b:
                    $h = 60 * (($g - $b) / $d + 4);
                    break;
            }
        }

        return ['h' => round($h, 2), 's' => round($s, 2), 'l' => round($l, 2)];
    }

    public static function hueAreInSameInterval($hue1, $hue2, $interval = 30)
    {
        return (round(($hue1 / $interval), 0, PHP_ROUND_HALF_DOWN)) === (round(($hue2 / $interval), 0, PHP_ROUND_HALF_DOWN));
    }

    public static function percent($number, $percent)
    {
        return round($number * $percent / 100);
    }
}