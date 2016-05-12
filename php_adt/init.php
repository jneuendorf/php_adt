<?php

// include this file for using all the classes that come with php_adt (Arr, Dict, Genewrapor, Set, Tree) (namespaced)

$namespaced = true;

$orig_include_path = get_include_path();
set_include_path(__DIR__.'/..');

require_once '_php_adt/init.php';
require_once 'php_adt/Arr.ns.php';
require_once 'php_adt/Dict.ns.php';
require_once 'php_adt/Genewrapor.ns.php';
require_once 'php_adt/Set.ns.php';
require_once 'php_adt/Tree.ns.php';

set_include_path($orig_include_path);
