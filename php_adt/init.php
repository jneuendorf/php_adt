<?php

// include this file for using all the classes that come with php_adt (Arr, Dict, Genewrapor, Set, Tree)
$orig_include_path = get_include_path();
set_include_path(__DIR__.'/..');

require_once '_php_adt/init.php';
require_once 'php_adt/Arr.php';
require_once 'php_adt/CharArr.php';
require_once 'php_adt/Dict.php';
require_once 'php_adt/Genewrapor.php';
require_once 'php_adt/Set.php';
require_once 'php_adt/Tree.php';

set_include_path($orig_include_path);
