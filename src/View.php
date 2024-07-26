<?php 
namespace Ralph;

use Engine;

class View{
    public static function render(
        $viewName, 
        $data = []
    ){
        return Engine::view($viewName, $data);
    }
}