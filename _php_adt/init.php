<?php

set_include_path(__DIR__.'/..');


// function exception_error_handler($severity, $message, $file, $line) {
function exception_error_handler($errno, $errstr, $errfile, $errline) {
    // if (!(error_reporting() & $severity)) {
    //     // This error code is not included in error_reporting
    //     return;
    // }
    // throw new ErrorException($message, 0, $severity, $file, $line);
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    // }
}
set_error_handler("exception_error_handler");


function import($class, $subdir='') {
    global $__namespaced;
    $path = get_include_path();

    if (strlen($subdir) > 0 && $subdir[0] != '/') {
        $subdir = '/'.$subdir;
    }

    // path is the same because when the include_path will differs depending on $__namespaced (see init.php files)
    $dir = $path.$subdir;

    if ($__namespaced) {
        $dir = $path.'/php_adt'.$subdir;
        $class = $class.'.ns.php';
    }
    else {
        $class = $class.'.php';
    }

    if (file_exists($dir.'/'.$class)) {
        require_once $dir.'/'.$class;
        return $dir.'/'.$class;
    }
    $class2 = str_replace('.ns', '', $class);
    if (file_exists($dir.'/'.$class2)) {
        require_once $dir.'/'.$class2;
        return $dir.'/'.$class2;
    }
    throw new Exception("Could not import file. Tried '$class' and '$class2' in '$dir'.", 1);
}


define('undefined', uniqid('undefined',true));

require_once 'funcs.php';
