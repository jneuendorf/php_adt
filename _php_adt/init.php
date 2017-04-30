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


define('undefined', uniqid('undefined', true));

require_once 'funcs.php';
