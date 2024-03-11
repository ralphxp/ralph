<?php
namespace Bird\Ralph;


class Engine{

    static $cache_path = 'cache/';
    static $cache_enabled = FALSE;
    static $yields = [];

    public static function view(
        $viewName, 
        $data = []
    ){
        extract($data, EXTR_SKIP);
        $viewpath = "views/";
        $file = $viewpath.$viewName;
        $cached_file = self::cache($file);
	    extract($data, EXTR_SKIP);
	   	require $cached_file;
        
    }

    static function cache($file) {
		if (!file_exists(self::$cache_path)) {
		  	mkdir(self::$cache_path, 0744);
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
        $content = str_replace("{{", "<?php ", $content);
        $content = str_replace("}}", " ?>", $content);
        $content = self::parseIf($content);
        $content = self::parseEcho( $content);

        return $content;
    }

    public static function parseIf($content)
    {
        $content = preg_replace('/@if\(\s*(.+?)\s*\)/i', "<?php if($1): ?> ", $content);
        $content = str_replace('@endif', "<?php endif; ?>", $content);
        return $content;
    }

    public static function parseEcho($content)
    {
        $content = preg_replace('/__\(\s*(.+?)\s*\)/i', ' echo $1; ', $content);
        return $content;
    }

    public static function parseTemplate($file)
    {
        $content = self::loadFile($file);
        $code = $content;
		preg_match_all('/[@#](template|extends)\([\s*\'\"](.+?)[\'\"\s*]\)/i', $content, $matches, PREG_SET_ORDER);
        if(count($matches) > 0){
            $template = $matches[0][2];
            $template = 'views/'.str_replace(".",'/', $template);
            $template = file_get_contents($template.'.bird.php');
            $content = preg_replace('/[@#](template|extends)\(\s*(.+?)\s*\)/i', '', $content);
        

            $code = self::parseYield($template, $content);
		}
        

        return $code;
    }

    public static function parseYield($code, $content)
    {

        preg_match_all('/[@#]yield\([\s*\'\"](.+?)[\s*\'"]\)/i', $code, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $yield) {
            $value = $yield[1];
            $section = self::parseSection($content, $value);
            $code = preg_replace('/[@#]yield\([\s*\'\"]'.$value.'[\s*\'"]\)/i', $section, $code);
        }   
        
        return $code;

    }

    public static function parseSection($code, $key)
    {
        preg_match_all('/[@#]section\([\s*\'\"]'.$key.'[\s*\'"]\)(.+?)[@#]endsection/s', $code, $matches, PREG_SET_ORDER);
    return ($matches[0][1]);
    }

    public static function loadFile($file){
        $content = file_get_contents($file.'.bird.php');
        return $content;
    }

    public static function parseIncludes($content){
        // echo $content;
		preg_match_all('/@(include|includes|require)\([\s*\'\"](.+?)[\'\"\s*]\)/i', $content, $matches, PREG_SET_ORDER);
        
		foreach ($matches as $value) {
            $inc = __DIR__.'/views/'.str_replace(".",'/',$value[2]);
			$content = str_replace($value[0], self::loadFile($inc), $content);
		}
		$content = preg_replace('/@(include|includes|require)\([\s*\'\"](.+?)[\'\"\s*]\)/i', '', $content);
		
        return $content;
    }
    
}