<?php

/* My MVC :=/ */
defined("ROOT_CONTROLLER") || define("ROOT_CONTROLLER", "Texel");
defined("ROOT_METHOD") || define("ROOT_METHOD", "index");

/* Shape types */
defined("SHAPE")       || define("SHAPE", [
    'RECTANGLE'     => 1,
    'ELLIPSE'       => 2,
    'TRIANGLE'      => 3,
]);

/* Settings */
defined("INITIAL_STATE") || define("INITIAL_STATE", [
    'width'         => 1080,
    'height'        => 1080,
    'text'          => 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Aliquam laborum vitae illo animi distinctio consectetur asperiores voluptatum exercitationem. Excepturi incidunt consectetur ut quaerat voluptate? Corporis magnam totam quos minus earum!',
    'image'         => false,
    'iterations'    => 1,
    'shape'         => 2,
    'repeating'     => 1,
    'iterations'    => 1
]);

/* Render Path */
defined("RENDER_PATH") || define("RENDER_PATH", __DIR__ ."/render/");

define('BASEURL', realpath(__DIR__));