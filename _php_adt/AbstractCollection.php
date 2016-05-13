<?php
/**
* @package _php_adt
*/
namespace _php_adt;

require_once '_php_adt/AbstractSet.php';

use \_php_adt\AbstractSet as AbstractSet;

/**
* Interface for AbstractMap, Arr, Set implementing is_empty() and size().
*/
abstract class AbstractCollection extends AbstractSet {
    abstract public function add(...$elements);
    // abstract public function equals($collection);
    abstract public function has($element);
    abstract public function map($callback);
    abstract public function remove($element);

    public function size() {
        return $this->_size;
    }

}
