<?php

require_once 'Collection.php';
require_once 'funcs.php';

class Arr implements Collection, ArrayAccess {

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
        'array_reverse',
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

    function __construct(...$elements) {
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
        // TODO
    }

    public function has($object) {
        return $this->index($object) >= 0;
    }

    public function hash() {
        $result = 0;
        foreach ($this->$elements as $idx => $element) {
            $result += ($idx + 1) * hash($element);
        }
        return $result;
    }

    public function is_empty() {
        return $this->_length === 0;
    }

    public function remove($object) {
        $idx = $this->index($object);
        if ($idx >= 0) {
            array_splice($this->elements, $idx, 1);
            $this->_length--;
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

    public function current() {
        return current($this->elements);
    }

    // API-CHANGE: each function not implemented

    public function end() {
        return end($this->elements);
    }

    // API-CHANGE: extract function not implemented

    public function key() {
        return key($this->elements);
    }

    public function key_exists($key) {
        if (is_int($key)) {
            return $key < $this->_length;
        }
        return false;
    }

    public function map($callback) {
        return new Arr(...array_map($callback, $this->elements));
    }

    public function next() {
        return next($this->elements);
    }

    public function pop() {
        if ($this->_length > 0) {
            $removed_element = array_pop($this->elements);
            $this->_length--;
            return $removed_element;
        }
        return null;
    }

    public function pos() {
        return pos($this->elements);
    }

    public function prev() {
        return prev($this->elements);
    }

    // API-CHANGE: chainable, @return $this instead of $new_length
    public function push(...$args) {
        $new_length = array_push($this->elements, ...$args);
        $this->_length += count($args);
        // return $new_length;
        return $this;
    }

    // API-CHANGE: chainable
    public function reset() {
        reset($this->elements);
        return $this;
    }

    public function search($needle, ...$args) {
        return $this->index($needle, ...$args);
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

    public function sort($cmp_function='mergesort_compare') {
        mergesort($this->elements, $cmp_function);
        return $this;
    }

    public function splice($offset, $length=null, $replacement = []) {
        if ($length === null) {
            $length = $this->_length - $offset;
        }
        $removed_elements = array_shift($this->elements, $offset, $length, $replacement);
        $this->_length += -count($removed_elements) + count($replacement);
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

    // EXTENDING THE API: custom delegations to native methods

    public function index($needle, ...$args) {
        $idx = array_search($needle, $this->elements, ...$args);
        if ($idx !== false) {
            return $idx;
        }
        return null;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED

    // protected function _mergesort($cmp_function = 'mergesort_compare') {
    //     // Arrays of size < 2 require no action.
    //     if (count($this->elements) < 2)
    //         return;
    //
    //     // Split the array in half
    //     $halfway = count($this->elements) / 2;
    //     $array1 = array_slice($this->elements, 0, $halfway);
    //     $array2 = array_slice($this->elements, $halfway);
    //     // Recurse to sort the two halves
    //     $this->_mergesort($array1, $cmp_function);
    //     $this->_mergesort($array2, $cmp_function);
    //     // If all of $array1 is <= all of $array2, just append them.
    //     if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
    //         $this->elements = array_merge($array1, $array2);
    //         return;
    //     }
    //     // Merge the two sorted arrays into a single sorted array
    //     $this->elements = array();
    //     $ptr1 = $ptr2 = 0;
    //     $len1 = count($array1);
    //     $len2 = count($array2);
    //     while ($ptr1 < $len1 && $ptr2 < $len2) {
    //         if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
    //             $this->elements[] = $array1[$ptr1++];
    //         }
    //         else {
    //             $this->elements[] = $array2[$ptr2++];
    //         }
    //     }
    //     // Merge the remainder
    //     while ($ptr1 < $len1) {
    //         $this->elements[] = $array1[$ptr1++];
    //     }
    //     while ($ptr2 < $len2) {
    //         $this->elements[] = $array2[$ptr2++];
    //     }
    // }

}

?>
