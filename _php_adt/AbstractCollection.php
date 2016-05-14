<?php
/**
* @package _php_adt
*/
namespace _php_adt;

// require_once '_php_adt/AbstractSequence.php';
require_once '_php_adt/Super.php';

// use \_php_adt\AbstractSequence as AbstractSequence;
// use \_php_adt\Super as Super;

/**
* Interface for AbstractMap, Arr, Set implementing a standard size() method.
*/
abstract class AbstractCollection extends Super {
    abstract public function add(...$elements);
    // abstract public function equals($collection);
    abstract public function has($element);
    abstract public function map($callback);
    abstract public function remove($element);

    public function size() {
        return $this->_size;
    }

}
