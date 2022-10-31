<?php
require "../autoload.php";

$URI = explode("/", parse_url(str_replace("/index.php", "", $_SERVER["REQUEST_URI"]), PHP_URL_PATH));
// dd($URI);
$contoller = empty($URI[2]) ? ROOT_CONTROLLER : $URI[2];
$method = empty($URI[3]) ? ROOT_METHOD : $URI[3];
$params = "[" . implode(",", array_slice($URI, 4)) . "]";

$call = "Controller::" . ucfirst($contoller);

return $call($method, $params);