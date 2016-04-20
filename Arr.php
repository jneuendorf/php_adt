<?php

require_once 'init.php';
require_once 'AbstractCollection.php';

class Arr extends AbstractCollection implements ArrayAccess, Iterator {

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

    protected $_elements = [];
    protected $_position;

    public function __construct(...$elements) {
        foreach ($elements as $idx => $element) {
            if (is_array($element)) {
                $element = new Arr(...$element);
            }
            array_push($this->_elements, $element);
        }
        $this->_size = count($this->_elements);
    }

    public function __get($name) {
        switch ($name) {
            case 'length':
                return $this->_size;
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
            $res = call_user_func($name, $this->_elements, ...$args);
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
        return __toString($this->_elements);
    }

    // use ...$args workaround for passing an optional parameter (incl. null)
    public function first(...$args) {
        if ($this->_size >= 1) {
            return $this->_elements[0];
        }
        // trigger exception
        if (count($args) === 0) {
            return $this->_elements[0];
        }
        // use default value
        return $args[0];
    }

    public function second(...$args) {
        if ($this->_size >= 2) {
            return $this->_elements[1];
        }
        if (count($args) === 0) {
            return $this->_elements[1];
        }
        return $args[0];
    }

    public function third(...$args) {
        if ($this->_size >= 3) {
            return $this->_elements[2];
        }
        if (count($args) === 0) {
            return $this->_elements[2];
        }
        return $args[0];
    }

    public function penultimate(...$args) {
        if ($this->_size >= 2) {
            return $this->_elements[$this->_size - 2];
        }
        if (count($args) === 0) {
            return $this->_elements[$this->_size - 2];
        }
        return $args[0];
    }

    public function last(...$args) {
        if ($this->_size >= 1) {
            return $this->_elements[$this->_size - 1];
        }
        if (count($args) === 0) {
            return $this->_elements[$this->_size - 1];
        }
        return $args[0];
    }


    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COLLECTION

    public function add($object) {
        return $this->push($object);
    }

    public function copy($deep=false) {
        if (!$deep) {
            return new Arr(...$this->_elements);
        }
        $res = new Arr();
        foreach ($this->_elements as $idx => $value) {
            $res->merge(__clone($value));
        }
        return $res;
    }

    public function clear() {
        $this->_elements = [];
        $this->_size = 0;
        return $this;
    }

    public function equals($arr) {
        if ($arr instanceof Arr) {
            if ($this->size() !== $arr->size()) {
                return false;
            }
            if (__hash($arr) !== __hash($this)) {
                return false;
            }
            // hashes are equal => compare each entry
            foreach ($this as $idx => $element) {
                if (!__equals($element, $arr[$idx])) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function has($object) {
        return $this->index($object) >= 0;
    }

    public function hash() {
        $result = 0;
        foreach ($this->_elements as $idx => $element) {
            $result += ($idx + 1) * __hash($element);
        }
        return $result;
    }

    public function remove($object) {
        $index = $this->index($object);
        if ($index !== null) {
            $this->splice($index, 1);
        }
        return $this;
    }

    public function size() {
        return $this->_size;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ARRAYACCESS

    protected function _adjust_offset($offset) {
        if ($this->offsetExists($offset)) {
            if ($offset < 0) {
                $offset += $this->_size;
            }
            return $offset;
        }
        throw new Exception("Undefined offset $offset!", 1);
    }

    protected function _get_start_end_from_offset($offset) {
        if (is_array($offset)) {
            if (is_int($offset[0]) && is_int($offset[1])) {
                $use_slicing = true;
                $start = $offset[0];
                $end = $offset[1];
            }
            else {
                throw new Exception('Invalid array offset '.__toString($offset).'. Array offsets must have the form \'[int1,int2]\'.');
            }
        }
        else if (is_string($offset)) {
            $offset = preg_replace('/\s+/', '', $offset);
            if (preg_match('/^\-?\d+\:\-?\d+$/', $offset) === 1) {
                $use_slicing = true;
                $parts = explode(':', $offset);
                $start = (int) $parts[0];
                $end = (int) $parts[1];
            }
            else {
                throw new Exception('Invalid string offset \''.$offset.'\'. String offsets must have the form \'int1:int2\'.');
            }
        }
        else if (is_int($offset)) {
            $use_slicing = false;
            $start = $offset;
            $end = $offset;
        }
        else {
            throw new Exception('Invalid offset. Use null ($a[]=4) to push, int for index access or for slicing use [start, end] or \'start:end\'!');
        }
        return [
            'start' => $this->_adjust_offset($start),
            'end' => $this->_adjust_offset($end),
            'slicing' => $use_slicing
        ];
    }

    public function offsetExists($offset) {
        if (is_int($offset)) {
            if ($offset >= 0) {
                return $offset < $this->_size;
            }
            // else: negative
            return abs($offset) <= $this->_size;
        }
        return false;
    }

    public function offsetGet($offset) {
        $bounds = $this->_get_start_end_from_offset($offset);
        if (!$bounds['slicing']) {
            return $this->_elements[$bounds['start']];
        }
        return $this->slice($bounds['start'], $bounds['end'] - $bounds['start']);
    }

    public function offsetSet($offset, $value) {
        // called like $my_arr[] = 2; => push
        if ($offset === null) {
            $this->push($value);
        }
        else {
            $this->_elements[$this->_adjust_offset($offset)] = $value;
        }
        return $this;
    }

    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->_elements[$offset]);
            $this->_size--;
            // reassign keys
            $this->_elements = array_values($this->_elements);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING PSEUDO INTERFACE CLONABLE

    public function __clone() {
        return $this->copy();
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    public function current() {
        return $this->_elements[$this->_position];
    }

    public function key() {
        return $this->_position;
    }

    public function next() {
        $this->_position++;
    }

    public function rewind() {
        $this->_position = 0;
    }

    public function valid() {
        return $this->_position >= 0 && $this->_position < $this->_size;
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

    // API-CHANGE: new function
    public function concat(...$arrays) {
        $res = new Arr();
        foreach ($arrays as $idx => $arr) {
            $res->merge(...$arrays);
        }
        return $res;
    }

    // current() is defined above (iterator interface section)
    // API-CHANGE: each function not implemented

    public function end() {
        $this->_position = $this->_size - 1;
        return $this;
    }

    // API-CHANGE: new function
    public function flatten($deep=false) {
        $flattened = new Arr();

        foreach ($this as $idx => $value) {
            if ($value instanceof Arr) {
                if (!$deep) {
                    $flattened->merge($value);
                }
                else {
                    $flattened->merge($value->flatten());
                }
            }
        }
        return $flattened;
    }

    // API-CHANGE: extract function not implemented
    // key() is defined above (iterator interface section)

    public function key_exists($key) {
        if (is_int($key)) {
            return $key < $this->_size;
        }
        return false;
    }

    public function map($callback) {
        return new Arr(...array_map($callback, $this->_elements));
    }

    // API-CHANGE: now in place (for not in place see concat)
    public function merge(...$arrays) {
        foreach ($arrays as $idx => $arr) {
            if ($arr instanceof self) {
                foreach ($arr as $i => $element) {
                    $this->push($element);
                }
            }
            elseif (is_array($arr)) {
                $this->push(...$arr);
            }
        }
        return $this;
    }

    // next() is defined above (iterator interface section)

    public function pop($index=null) {
        if ($index === null) {
            $index = $this->_size - 1;
        }
        $removed_elements = $this->splice($index, 1);
        return $removed_elements[0];
    }

    public function pos() {
        return $this->current();
    }

    public function prev() {
        $this->_position--;
        if ($this->_position >= 0 && $this->_position < $this->_size) {
            return $this->_elements[$this->_position];
        }
        throw new Exception("Arr::next: Invalid position", 1);
    }

    // API-CHANGE: chainable, @return $this instead of $new_length
    public function push(...$args) {
        $new_length = array_push($this->_elements, ...$args);
        $this->_size = $new_length;
        return $this;
    }

    // API-CHANGE: chainable
    public function reset() {
        return $this->rewind();
    }

    // name in php array was reverse which is not in place.
    public function reversed() {
        return new static(...array_reverse($this->_elements));
    }

    // API-CHANGE: in place
    public function reverse() {
        $left = 0;
        $right = $this->_size - 1;
        $arr = &$this->_elements;
        while ($left < $right) {
            $temp = $arr[$left];
            $arr[$left] = $arr[$right];
            $arr[$right] = $temp;
            $left++;
            $right--;
       }
        return $this;
    }

    public function search(...$args) {
        return $this->index(...$args);
    }

    public function shift() {
        if ($this->_size > 0) {
            $removed_element = array_shift($this->_elements);
            $this->_size--;
            return new static($removed_element);
        }
        return null;
    }

    public function shuffle() {
        if (shuffle($this->_elements)) {
            return $this;
        }
        throw new Exception("Arr::shuffle: Some unknow error during shuffle.", 1);
    }

    public function sort($cmp_function='__mergesort_compare') {
        __mergesort($this->_elements, $cmp_function);
        return $this;
    }

    // API-CHANGE: if $length is not given does NOT remove everything after $offset (including $offset) but does not remove anything
    // API-CHANGE: inserted elements are passed as separate parameters - not as an array of elements
    public function splice($offset, $length=0, ...$new_elements) {
        // if ($length === null) {
        //     $length = $this->_size - $offset;
        // }
        $removed_elements = array_splice($this->_elements, $offset, $length, $new_elements);
        $this->_size += -count($removed_elements) + count($new_elements);
        return new static($removed_elements);
    }

    // API-CHANGE: @return $this instead of $new_length
    public function unshift(...$args) {
        $length = array_unshift($this->_elements, ...$args);
        $this->_size += $length;
        return $this;
    }

    // API-CHANGE: @throws Exception
    public function walk_recursive(...$args) {
        if (array_walk_recursive($this->_elements, ...$args)) {
            return $this;
        }
        throw new Exception("Arr::walk_recursive: Some unknow error during recursion.", 1);
    }

    public function walk(...$args) {
        if (array_walk($this->_elements, ...$args)) {
            return $this;
        }
        throw new Exception("Arr::walk_recursive: Some unknow error during recursion.", 1);
    }
    // API-CHANGE: new function
    public function without(...$args) {
        $res = new Arr();
        foreach ($this as $idx => $element) {
            if (!in_array($element, $args)) {
                $res->push($element);
            }
        }
        return $res;
    }

    //////////////////////////////////////////////////////////////////////////////////////////
    // EXTENDING THE API: adapt to python mutable sequence API

    public function append(...$args) {
        return $this->push(...$args);
    }

    // clear() is implemented above (collection interface section)

    public function extend($iterable) {
        foreach ($iterable as $key => $value) {
            $new_length = array_push($this->_elements, $value);
        }
        $this->_size = $new_length;
        return $this;
    }

    public function index($needle, $start=0, $stop=null) {
        if ($stop === null) {
            $stop = $this->_size - 1;
        }
        if ($start === 0 && $stop === $this->_size - 1) {
            $idx = array_search($needle, $this->_elements, true);
        }
        else {
            $idx = array_search($needle, array_slice($this->_elements, $start, $stop + 1), true);
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
