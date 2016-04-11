<?php

require_once 'Collection.php';
require_once 'funcs.php';

class Arr extends Collection implements ArrayAccess, Iterator {

    // list of native array function that we can automatically create delegations (using the __callStatic() method)
    protected static $class_methods = [
        'array_combine',
        'array_fill_keys',
        'array_fill',
        'array_flip',
    ];

    // list of native array function that we can automatically create delegations (using the __call() method)
    protected static $instance_methods = [
        'array_chunk',
        'array_column',
        'array_count_values',
        'array_diff_assoc',
        'array_diff_key',
        'array_diff_uassoc',
        'array_diff_ukey',
        'array_diff',
        'array_filter',
        'array_intersect',
        'array_keys',
        'array_merge_recursive',
        'array_merge',
        'array_pad',
        'array_product',
        'array_rand',
        'array_reduce',
        'array_replace_recursive',
        'array_replace',
        'array_search',
        'array_slice',
        'array_sum',
        'array_udiff_assoc',
        'array_udiff_uassoc',
        'array_udiff',
        'array_uintersect_assoc',
        'array_uintersect_uassoc',
        'array_uintersect',
        'array_unique',
        'array_values',
    ];

    protected $elements = [];
    protected $_length;
    protected $_position;

    public function __construct(...$elements) {
        foreach ($elements as $idx => $element) {
            if (is_array($element)) {
                $element = new Arr(...$element);
            }
            array_push($this->elements, $element);
        }
        $this->_length = count($this->elements);
    }

    public function __get($name) {
        switch ($name) {
            case 'length':
                return $this->_length;
            default:
                return null;
        }
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'length':
                throw new Exception("Cannot set length property of Arr!", 1);
            default:
                return $this;
        }
    }

    public function __call($name, $args) {
        $org_name = $name;
        $name = 'array_'.$name;
        if (in_array($name, static::$instance_methods)) {
            $res = call_user_func($name, $this->elements, ...$args);
            if (is_array($res)) {
                return new Arr(...$res);
            }
            return $res;
        }
        throw new Exception("Cannot call $org_name on instance of Arr!", 1);
    }

    public static function __callStatic($name, $args) {
        $org_name = $name;
        $name = 'array_'.$name;
        if (in_array($name, static::$class_methods)) {
            return new Arr(...call_user_func($name, ...$args));
        }
        throw new Exception("Cannot call $org_name on the Arr class!", 1);
    }

    public function __toString() {
        return '['.implode(', ', $this->elements).']';
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COLLECTION

    public function add($object) {
        return $this->push($object);
    }

    public function clear() {
        $this->elements = [];
        $this->_length = 0;
        return $this;
    }

    public function equals($collection) {
        if ($collection instanceof Collection) {
            return __hash($collection) === __hash($this);
        }
        return false;
    }

    public function has($object) {
        return $this->index($object) >= 0;
    }

    public function hash() {
        $result = 0;
        foreach ($this->elements as $idx => $element) {
            $result += ($idx + 1) * hash($element);
        }
        return $result;
    }

    public function is_empty() {
        return $this->_length === 0;
    }

    public function remove($object) {
        $index = $this->index($object);
        if ($index !== null) {
            $this->splice($index, 1);
        }
        return $this;
    }

    public function size() {
        return $this->_length;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ARRAYACCESS

    protected function _adjust_offset($offset) {
        if ($this->offsetExists($offset)) {
            if ($offset < 0) {
                $offset += $this->_length;
            }
            return $offset;
        }
        throw new Exception("Undefined offset $offset!", 1);
    }

    public function offsetExists($offset) {
        if (is_int($offset)) {
            if ($offset >= 0) {
                return $offset < $this->_length;
            }
            // else: negative
            return abs($offset) <= $this->_length;
        }
        return false;
    }

    public function offsetGet($offset) {
        return $this->elements[$this->_adjust_offset($offset)];
    }

    public function offsetSet($offset, $value) {
        // called like $my_arr[] = 2; => push
        if ($offset === null) {
            $this->push($value);
        }
        else {
            $this->elements[$this->_adjust_offset($offset)] = $value;
        }
        return $this;
    }

    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->elements[$offset]);
            $this->_length--;
            // reassign keys
            $this->elements = array_values($this->elements);
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    public function current() {
        if ($this->_position >= 0 && $this->_position < $this->_length) {
            return $this->elements[$this->_position];
        }
        // i would like to throw an exception to differentiate a possible NULL value from out of range but this would cause uncaught exception when iterating...
        return null;
    }

    public function key() {
        if ($this->_position >= 0 && $this->_position < $this->_length) {
            return $this->_position;
        }
        return null;
    }

    public function next() {
        $this->_position++;
        if ($this->_position >= 0 && $this->_position < $this->_length) {
            return $this->elements[$this->_position];
        }
        // i would like to throw an exception to differentiate a possible NULL value from out of range but this would cause uncaught exception when iterating...
        return null;
    }

    public function rewind() {
        $this->_position = 0;
        return $this;
    }

    public function valid() {
        return $this->_position >= 0 && $this->_position < $this->_length;
    }


    ////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////
    // DELEGATIONS TO NATIVE METHODS

    ////////////////////////////////////////////////////////////////////////////////////
    // STATIC

    public static function range(...$args) {
        return new static(...range(...$args));
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // INSTANCE

    // custom delegations to native methods

    public function change_key_case(...$args) {
        // do nothing because all our keys are numbers
        return $this;
    }

    // current() is defined above (iterator interface section)
    // API-CHANGE: each function not implemented

    public function end() {
        $this->_position = $this->_length - 1;
        return $this;
    }

    // API-CHANGE: extract function not implemented
    // key() is defined above (iterator interface section)

    public function key_exists($key) {
        if (is_int($key)) {
            return $key < $this->_length;
        }
        return false;
    }

    public function map($callback) {
        return new Arr(...array_map($callback, $this->elements));
    }

    // next() is defined above (iterator interface section)

    public function pop($index=null) {
        if ($index === null) {
            $index = $this->_length - 1;
        }
        $removed_elements = $this->splice($index, 1);
        return $removed_elements[0];
    }

    public function pos() {
        return $this->current();
    }

    public function prev() {
        $this->_position--;
        if ($this->_position >= 0 && $this->_position < $this->_length) {
            return $this->elements[$this->_position];
        }
        throw new Exception("Arr::next: Invalid position", 1);
    }

    // API-CHANGE: chainable, @return $this instead of $new_length
    public function push(...$args) {
        $new_length = array_push($this->elements, ...$args);
        $this->_length = $new_length;
        return $this;
    }

    // API-CHANGE: chainable
    public function reset() {
        return $this->rewind();
    }

    // name in php array was reverse which is not in place.
    public function reversed() {
        return new static(...array_reverse($this->elements));
    }

    // API-CHANGE: in place
    public function reverse() {
        $left = 0;
        $right = $this->_length - 1;
        $arr = &$this->elements;
        while ($left < $right) {
            $temp = $arr[$left];
            $arr[$left] = $arr[$right];
            $arr[$right] = $temp;
            $left++;
            $right--;
       }
        return new static(...array_reverse($this->elements));
    }

    public function search(...$args) {
        return $this->index(...$args);
    }

    public function shift() {
        if ($this->_length > 0) {
            $removed_element = array_shift($this->elements);
            $this->_length--;
            return new static($removed_element);
        }
        return null;
    }

    public function shuffle() {
        if (shuffle($this->elements)) {
            return $this;
        }
        throw new Exception("Arr::shuffle: Some unknow error during shuffle.", 1);
    }

    public function sort($cmp_function='__mergesort_compare') {
        __mergesort($this->elements, $cmp_function);
        return $this;
    }

    // API-CHANGE: if $length is not given does NOT remove everything after $offset (including $offset) but does not remove anything
    // API-CHANGE: inserted elements are passed as separate parameters - not as an array of elements
    public function splice($offset, $length=0, ...$new_elements) {
        // if ($length === null) {
        //     $length = $this->_length - $offset;
        // }
        $removed_elements = array_splice($this->elements, $offset, $length, $new_elements);
        $this->_length += -count($removed_elements) + count($new_elements);
        return new static($removed_elements);
    }

    // API-CHANGE: @return $this instead of $new_length
    public function unshift(...$args) {
        $length = array_unshift($this->elements, ...$args);
        $this->_length += $length;
        return $this;
    }

    // API-CHANGE: @throws Exception
    public function walk_recursive(...$args) {
        if (array_walk_recursive($this->elements, ...$args)) {
            return $this;
        }
        throw new Exception("Arr::walk_recursive: Some unknow error during recursion.", 1);
    }

    public function walk(...$args) {
        if (array_walk($this->elements, ...$args)) {
            return $this;
        }
        throw new Exception("Arr::walk_recursive: Some unknow error during recursion.", 1);
    }

    // EXTENDING THE API: adapt to python mutable sequence API
    public function append(...$args) {
        return $this->push(...$args);
    }

    // clear() is implemented above (collection interface section)

    public function copy() {
        return new static(...$this->elements);
    }

    public function extend($iterable) {
        foreach ($iterable as $key => $value) {
            $new_length = array_push($this->elements, $value);
        }
        $this->_length = $new_length;
        return $this;
    }

    public function index($needle, $start=0, $stop=null) {
        if ($stop === null) {
            $stop = $this->_length - 1;
        }
        if ($start === 0 && $stop === $this->_length - 1) {
            $idx = array_search($needle, $this->elements, true);
        }
        else {
            $idx = array_search($needle, array_slice($this->elements, $start, $stop + 1), true);
        }

        if ($idx !== false) {
            return $idx;
        }
        return null;
    }

    public function insert($index, ...$elements) {
        return $this->splice($index, 0, ...$elements);
    }

    // pop([index]) is implemented above (php array section)
    // remove(object) is implemented above (colsection interface section)


    ////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED

}

?>
