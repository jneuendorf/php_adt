<?php

require_once 'Super.php';

abstract class AbstractCollection extends Super {
    abstract public function add(...$elements);
    abstract public function clear();
    // abstract public function copy($deep=false);
    abstract public function equals($collection);
    abstract public function has($element);
    abstract public function map($callback);
    // abstract public function hash();
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
