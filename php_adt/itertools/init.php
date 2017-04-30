<?php

// include this file for using the itertools functionality
$orig_include_path = get_include_path();
set_include_path(__DIR__.'/../..');

require_once 'php_adt/itertools/StopIteration.php';
require_once 'php_adt/itertools/Slice.php';
require_once 'php_adt/itertools/itertools.php';

set_include_path($orig_include_path);
