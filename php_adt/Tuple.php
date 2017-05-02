<?php

namespace php_adt;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '_php_adt', 'AbstractSequence.php']);
use \_php_adt\AbstractSequence;
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'exceptions', 'ValueError.php']);
use \php_adt\ValueError;


class Tuple extends AbstractSequence {

    protected $_items;

    public function __construct($iterable=[]) {
        $args = func_get_args();
        if (count($args) === 1) {
            $this->_items = Arr::from_iterable($iterable);
        }
        else {
            $this->_items = Arr::from_iterable($args);
        }
    }

    public function hash() {
        return $this->_items->hash() * 7;
    }

    /**
    * Indicates whether the Arr instance is equals to another object.
    * @param mixed $arr
    * @return bool
    */
    public function equals($tuple) {
        if (is_object($tuple) && $tuple instanceof self) {
            return $this->_items->equals($tuple->to_arr());
        }
        return false;
    }

    public function size() {
        return count($this->_items);
    }

    public function copy($deep=False) {
        return new static($this->_items);
    }

    public function slice($start=0, $length=null) {
        return new static($this->_items->slice($start, $length));
    }

    public function to_a() {
        return $this->_items->to_a();
    }

    public function to_arr() {
        return $this->_items->copy();
    }

    public function to_set() {
        return $this->_items->to_set();
    }

    public function to_str() {
        return $this->__str__();
    }

    public function to_dict() {
        return $this->_items->to_dict();
    }

    public function _get_at($index) {
        return $this->_items->get($index);
    }

    /**
    * Returns the first index of given $needle or null if the $needle is not found.
    * @param mixed $needle
    * @param int $start Where to start searching (inclusive).
    * @param int $stop Where to stop searching (exclusive).
    * @param callable $equality This optional parameter can be used to define what elements is considered a match.
    * @return int
    */
    public function index($needle, $start=0, $stop=null, $equality='\php_adt\__equals') {
        if ($stop === null) {
            $stop = $this->size();
        }
        for ($i = $start; $i < $stop; $i++) {
            if (call_user_func($equality, $this->_items[$i], $needle) === true) {
                return $i;
            }
        }
        throw new \php_adt\ValueError(str($needle).' is not in tuple', 1);
    }

    // ARRAY ACCESS
    public function offsetSet($offset, $value) {
        throw new \Exception('Tuples are immutable', 1);
    }

    public function offsetUnset($offset) {
        throw new \Exception('Tuples are immutable', 1);
    }

    // ITERATOR
    public function current() {
        return $this->_items->current();
    }

    public function next() {
        return $this->_items->next();
    }

    public function key() {
        return $this->_items->key();
    }

    public function valid() {
        return $this->_items->valid();
    }

    public function rewind() {
        return $this->_items->rewind();
    }

    public function __str__() {
        return '('.
            $this->_items
                ->map(function($idx, $item) {
                    return str($item);
                })
                ->join(', ')
        .')';
    }
}

// for more trivial compilation of `tuple(1, 2)` to `new tuple(1, 2)`
class_alias(__NAMESPACE__.'\Tuple', 'tuple');
