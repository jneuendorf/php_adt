<?php

require_once 'Clonable.php';
require_once 'Hashable.php';

abstract class AbstractCollection extends Clonable implements Countable, Hashable {
    protected $_size = 0;


    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COUNTABLE

    public function count() {
        return $this->size();
    }

    abstract public function add(...$elements);
    abstract public function clear();
    // abstract public function copy($deep=false);
    abstract public function equals($collection);
    abstract public function has($element);
    // abstract public function hash();
    abstract public function remove($element);

    public function is_empty() {
        return $this->size() === 0;
    }

    public function size() {
        return $this->_size;
    }
}

?>
