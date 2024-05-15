<?php
namespace Codx\Ralph;


class Engine{

    static $cache_path = 'cache/';
    static $cache_enabled = FALSE;
    static $sections = '';

    static $tmp = '';

    public static function view(
        $viewName, 
        $data = []
    ){
        
        extract($data, EXTR_SKIP);
        $viewpath = getcwd()."/views/";
        $file = $viewpath.$viewName;
        $cached_file = self::cache($file);
	    extract($data, EXTR_SKIP);
        $message = array_key_exists('message', $data)? $data['message']: "";
	   	require $cached_file;
        
    }

    static function cache($file) {
		if (!file_exists(self::$cache_path)) {
		  	mkdir(getcwd().self::$cache_path, 0744);
		}
	    $cached_file = self::$cache_path . str_replace(array('/'), array('/'), 'cache.php');
	    if ( !self::$cache_enabled || !file_exists($cached_file) ) {
			$code = self::parseTemplate($file);
            $code = self::parseIncludes($code);
			$code = self::compileCode($code);
	        file_put_contents($cached_file, $code);
	    }
		return $cached_file;
	}

    public static function compileCode($content)
    {
        $content = str_replace("{{--", "<!-- ", $content);
        $content = str_replace("--}}", " -->", $content);
        $content = str_replace("{{", "<?= ", $content);
        $content = str_replace("}}", " ?>", $content);
        $content = str_replace("@php", "<?php ", $content);
        $content = str_replace("@endphp", " ?>", $content);
        $content = self::parseComment($content);
        $content = self::parseIf($content);
        $content = self::parseForEach($content);
        $content = self::parseEcho( $content);

        return $content;
    }

    public static function parseComment($code)
    {
        $code = preg_replace('/\{\# \s*(.+?)\s*(.+?)\#\}/i', '', $code);
       
        return $code;
    }

    public static function parseIf($content)
    {
        $content = preg_replace('/@if\s*\(\s*(.+?)\s*\)/i', "<?php if($1): ?> ", $content);
            $content = preg_replace('/@elseif\s*\(\s*(.+?)\s*\)/i', "<?php elseif($1): ?> ", $content);
                $content = preg_replace('/@else/i', "<?php else: ?> ", $content);
        $content = preg_replace('/@endif/i', "<?php endif ?> ", $content);
        return $content;
    }

    public static function parseForEach($content)
    {
        $content = preg_replace('/@foreach\s*\(\s*(.+?)\s*\)/i', "<?php foreach($1): ?> ", $content);
        $content = preg_replace('/@endforeach/i', "<?php endforeach ?> ", $content);
        return $content;
    }

    public static function parseEcho($content)
    {
        $content = preg_replace('/__\(\s*(.+?)\s*\)/i', '$1;', $content);
      
        return $content;
    }

    public static function parseTemplate($file)
    {
        $content = self::loadFile($file);
        $code = $content;

		preg_match_all('/[@#](template|extends)\([\s*\'\"](.+?)[\'\"\s*]\)/i', $content, $matches, PREG_SET_ORDER);
        if(count($matches) > 0){

            $template = $matches[0][2];

            

            $code = self::parseYield($template, $content);
		}

        

        return $code;
    }

    public static function parseYield($template, $content)
    {
        $template = getcwd().'/views/'.str_replace(".",'/', $template);
        $content = preg_replace('/[@#](template|extends)\([\s*\'\"](.+?)[\'\"\s*]\)/i', '', $content);

        preg_match_all('/[@#]section\([\s*\'\"](.+?)[\s*\'"]\)(.+?)[@#]endsection/s', $content, $sections, PREG_SET_ORDER);
        $template = file_get_contents($template.'.blade.php');
        $code = '';
        foreach($sections as $section => $cod){

            $c = $cod[0];
            $name = $cod[1];
            
            $template = preg_replace('/@yield\([\s*\'\"]'.$name.'[\s*\'\"]\)/i', $c, $template,);
            
            

        }

        $template = preg_replace('/@yield\([\s*\'\"](.+?)[\s*\'\"]\)/i', '', $template,);

        return self::parseSection($template);

    }

    public static function parseSection($code)
    {
        
        
        
        $code = preg_replace('/[@#]section\([\s*\'\"](.+?)[\s*\'"]\)/s', '', $code);
        $code = preg_replace('/[@#]endsection/s', '', $code);
        return $code;
        
    }

    public static function loadFile($file){
        $file = str_replace('.', '/', $file).'.blade.php';
        if(file_exists($file)) {
            
            
            $content = file_get_contents($file);
            return $content;
        } else {
            echo "View not found";
            die();
        }
        
    }

    public static function parseIncludes($content){
        // echo $content;
		preg_match_all('/@(include|includes|require)\([\s*\'\"](.+?)[\'\"\s*]\)/i', $content, $matches, PREG_SET_ORDER);
        
		foreach ($matches as $value) {
            $inc = getcwd().'/views/'.str_replace(".",'/',$value[2]);
			$content = str_replace($value[0], self::loadFile($inc), $content);
		}
		$content = preg_replace('/@(include|includes|require)\([\s*\'\"](.+?)[\'\"\s*]\)/i', '', $content);
		
        return $content;
    }
    
}