<?php
class Controller
{
    private function __construct()
    {
        
    }

    public static function __callStatic($controller, $parameter)
    {
        $controller = 'Controllers\\' . $controller;


        if(class_exists($controller)) {
            $controller = new $controller();
            
            if(method_exists($controller, $parameter[0])) {
                $method = function($params) use ($controller, $parameter) {
                    return $controller->{$parameter[0]}($params);
                };
    
                return call_user_func_array($method, array_slice($parameter, 1));
            }

        }
        
        show404();
    }
}