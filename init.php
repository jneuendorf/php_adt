<?php

// include this file for using all the classes that come with php_adt (Arr, Dict, Genewrapor, Set, Tree)  (non-namespaced)

$__namespaced = false;

$orig_include_path = get_include_path();
set_include_path(__DIR__);

require_once '_php_adt/init.php';
require_once 'Arr.php';
require_once 'CharArr.php';
require_once 'Dict.php';
require_once 'Genewrapor.php';
require_once 'Set.php';
require_once 'Tree.php';
// TODO: don't use `set_include_path` at all
// TODO: support PSR-4 autoloading?
// require_once __DIR__.DIRECTORY_SEPARATOR.'_php_adt/init.php';
// require_once __DIR__.DIRECTORY_SEPARATOR.'Arr.php';
// require_once __DIR__.DIRECTORY_SEPARATOR.'CharArr.php';
// require_once __DIR__.DIRECTORY_SEPARATOR.'Dict.php';
// require_once __DIR__.DIRECTORY_SEPARATOR.'Genewrapor.php';
// require_once __DIR__.DIRECTORY_SEPARATOR.'Set.php';
// require_once __DIR__.DIRECTORY_SEPARATOR.'Tree.php';

set_include_path($orig_include_path);
