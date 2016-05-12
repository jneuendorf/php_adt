<?php

// include this file for using the itertools functionality (non-namespaced)

$namespaced = false;

$orig_include_path = get_include_path();
set_include_path(__DIR__);

require_once 'itertools.php';
require_once 'itertools.StopIteration.php';
require_once 'itertools.Slice.php';

set_include_path($orig_include_path);
