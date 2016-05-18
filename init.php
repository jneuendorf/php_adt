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

set_include_path($orig_include_path);
