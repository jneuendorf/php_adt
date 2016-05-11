<?php

namespace _php_adt;

require_once 'Super.php';

/**
* Interface for AbstractMap, Arr, Set implementing is_empty() and size().
*/
abstract class AbstractCollection extends Super {
    abstract public function add(...$elements);
    abstract public function clear();
    abstract public function equals($collection);
    abstract public function has($element);
    abstract public function map($callback);
    abstract public function remove($element);

    public function is_empty() {
        return $this->size() === 0;
    }

    public function size() {
        return $this->_size;
    }

    abstract public function to_a();
    abstract public function to_arr();
    abstract public function to_set();
    abstract public function to_dict();
}
