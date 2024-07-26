<?php 
namespace Bird\Ralph;

use Engine;

class View{
    public static function render(
        $viewName, 
        $data = []
    ){
        Engine::view($viewName, $data);
    }
}