<?php

require_once 'Hashable.php';

abstract class AbstractCollection implements Countable, Hashable {
    protected $_size = 0;


    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COUNTABLE

    public function count() {
        return $this->_size;
    }

    public function add($element) {}
    public function clear() {}
    public function copy($deep=false) {}
    public function equals($collection) {}
    public function has($element) {}
    // public function hash() {}
    public function remove($element) {}

    public function is_empty() {
        return $this->size() === 0;
    }

    public function size() {
        return $this->_size;
    }
}

?>
