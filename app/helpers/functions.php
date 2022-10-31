<?php

function dd($data, $dump = false)
{
    echo "<pre>";
    $dump ? var_dump($data) : print_r($data);
    exit;
}

function keyValidation(array $keys, array $array)
{
    foreach($keys as $key)
        if(!array_key_exists($key, $array))
            return;
    
    return true;
}

function imageValidation($image = null)
{
    if(!$image) return;

    $imageFileType = strtolower(pathinfo($image['name'],PATHINFO_EXTENSION));
    
    if ($image["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        header("Content-Type: text/plain", true, 400);
        exit;
    }

    if($imageFileType != "jpg") {
        echo "Sorry, only JPG,JPEG files are allowed.";
        header("Content-Type: text/plain", true, 400);
        exit;
    }

    return $image;
}

function sizeValidation()
{
    @list($width, $height) = $_POST;
    $maxArea = pow(8192, 2);

    if($width * $height > $maxArea) {
        echo "Sorry, your sizes are too large.";
        header("Content-Type: text/plain", true, 400);
        exit;
    }
}

function textValidation()
{
    @list($text) = $_POST;

    if(strlen($text)) {
        echo "Text is empty.";
        header("Content-Type: text/plain", true, 400);
        exit;
    }
}

function show404()
{
    echo "Not Found";
    header("Content-Type: text/plain", true, 404);
    exit;
}

