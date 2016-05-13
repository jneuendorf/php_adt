<?php

/**
* @package _php_adt
*/
namespace _php_adt;

require_once '_php_adt/Super.php';

abstract class AbstractSet extends Super implements \ArrayAccess, \Iterator {
    abstract public function clear();

    abstract public function to_a();
    abstract public function to_arr();
    abstract public function to_set();
    abstract public function to_str();
    abstract public function to_dict();

}
