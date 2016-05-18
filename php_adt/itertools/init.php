<?php

// include this file for using the itertools functionality (namespaced)

$__namespaced = true;

$orig_include_path = get_include_path();
set_include_path(__DIR__.'/../..');

require_once 'php_adt/itertools/StopIteration.ns.php';
require_once 'php_adt/itertools/Slice.ns.php';
require_once 'php_adt/itertools/itertools.ns.php';

set_include_path($orig_include_path);
