<?php

require_once 'init.php';
require_once 'AbstractCollection.php';
require_once 'Dict.php';

class Arr extends AbstractCollection implements ArrayAccess, Iterator {

    // list of native array function that we can automatically create delegations (using the __callStatic() method)
    protected static $class_methods = [
        // 'array_fill',
    ];

    // list of native array function that we can automatically create delegations (using the __call() method)
    protected static $instance_methods = [
        'array_chunk',
        'array_column',
        'array_filter',
        'array_keys',
        'array_merge_recursive',
        'array_pad',
        'array_product',
        'array_rand',
        'array_reduce',
        'array_slice',
        'array_sum',
        // 'array_udiff',
        // 'array_uintersect',
        'array_unique',
        'array_values',
    ];

    protected $_elements = [];
    protected $_position;

    public function __construct(...$elements) {
        foreach ($elements as $idx => $element) {
            array_push($this->_elements, $element);
        }
        $this->_size = count($this->_elements);
    }

    // STATIC
    public static function __callStatic($name, $args) {
        $org_name = $name;
        $name = 'array_'.$name;
        if (in_array($name, static::$class_methods)) {
            return new Arr(...call_user_func($name, ...$args));
        }
        throw new Exception("Cannot call $org_name on the Arr class!", 1);
    }

    public static function from_array($array, $recursive=true) {
        $result = new Arr();
        foreach ($array as $key => $element) {
            if ($recursive && is_array($element)) {
                $element = Arr::from_array($element, $recursive);
            }
            $result->push($element);
        }
        return $result;
    }

    public static function fill($num, $value=null) {
        return new static(...array_fill(0, $num, $value));
    }

    public static function range(...$args) {
        return new static(...range(...$args));
    }

    // INSTANCE

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

    public function __toString() {
        return __toString($this->_elements);
    }

    public function elements() {
        return $this->_elements;
    }

    public function to_a() {
        $res = [];
        foreach ($this as $idx => $element) {
            if (is_object($element) && method_exists($element, 'to_a')) {
                $res[] = $element->to_a();
            }
            else {
                $res[] = $element;
            }
        }
        return $res;
    }

    public function to_arr() {
        return $this->copy();
    }

    public function to_dict() {
        return new Dict(null, $this);
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

    public function has($object, $equality='__equals') {
        return $this->index($object, 0, $this->_size, $equality) !== null;
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

    public function remove_at($index) {
        return $this->splice($index, 1);
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

    ////////////////////////////////////////////////////////////////////////////////////
    // INSTANCE

    // implementations based on the API of native methods
    // API-CHANGE: 'change_key_case' function not implemented

    // API-CHANGE: new function 'concat'
    public function concat(...$arrays) {
        $res = new Arr(...$this->_elements);
        foreach ($arrays as $idx => $arr) {
            $res->merge(...$arrays);
        }
        return $res;
    }

    // API-CHANGE: 'count_values': returns Dict
    public function count_values() {
        $res = new Dict();
        foreach ($this->group_by() as $key => $value) {
            $res->put($key, $value->size());
        }
        return $res;
    }

    public function diff($arr, $equality='__equals') {
        $res = new Arr();
        foreach ($this as $idx => $elem) {
            if (!$arr->has($elem, $equality)) {
                $res->push($elem);
            }
        }
        return $res;
    }

    // API-CHANGE: 'difference': alias for 'diff'
    public function difference(...$args) {
        return $this->diff(...$args);
    }

    public function group_by($group_func=null) {
        if ($group_func === null) {
            $group_func = function($elem) {
                return $elem;
            };
        }
        $dict = new Dict();
        foreach ($this as $idx => $elem) {
            $grouped = $group_func($elem);
            if ($dict->has($grouped)) {
                $dict->get($grouped)->push($elem);
            }
            else {
                $dict->put($grouped, new Arr($elem));
            }
        }
        return $dict;
    }

    // current() is defined above (iterator interface section)
    // API-CHANGE: 'each' function not implemented

    public function end() {
        $this->_position = $this->_size - 1;
        return $this;
    }

    // API-CHANGE: new function 'flatten'
    public function flatten($deep=false) {
        $flattened = new Arr();
        foreach ($this as $idx => $value) {
            if ($deep && $value instanceof Arr) {
                $flattened->merge($value->flatten());
            }
            else {
                $flattened->push($value);
            }
        }
        return $flattened;
    }

    // API-CHANGE: new function 'get'
    public function get($idx) {
        return $this->offsetGet($idx);
    }

    public function intersect($arr, $equality='__equals') {
        $res = new Arr();
        foreach ($this as $idx => $elem) {
            if ($arr->has($elem, $equality)) {
                $res->push($elem);
            }
        }
        return $res;
    }

    // API-CHANGE: 'extract' function not implemented
    // key() is defined above (iterator interface section)
    // API-CHANGE: 'key_exists' function not implemented

    public function map($callback) {
        return new Arr(...array_map($callback, $this->_elements));
    }

    // API-CHANGE: 'merge' is in place (not in place => concat)
    public function merge(...$arrs) {
        foreach ($arrs as $idx => $arr) {
            if ($arr instanceof self) {
                foreach ($arr as $i => $element) {
                    $this->push($element);
                }
            }
            else {
                $this->push($arr);
            }
        }
        return $this;
    }

    // API-CHANGE: 'merge_recursive' is in place (not in place => concat)
    public function merge_recursive($arr) {
        foreach ($arr as $i => $element) {
            if ($element instanceof self && $this->_elements[$i] instanceof self) {
                $this->_elements[$i]->merge($element);
            }
            else {
                $this->push($element);
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

    // API-CHANGE: 'push': is chainable (returns $this instead of $new_length)
    public function push(...$args) {
        $new_length = array_push($this->_elements, ...$args);
        $this->_size = $new_length;
        return $this;
    }

    // API-CHANGE: 'replace': takes Dict as set of replacements
    public function replace($replacements) {
        foreach ($replacements as $old_val => $new_val) {
            $idx = $this->index($old_val);
            while ($idx !== null) {
                $this->_elements[$idx] = $new_val;
                $idx = $this->index($old_val);
            }
        }
        return $this;
    }

    // API-CHANGE: 'replace_recursive': not implemented

    // API-CHANGE: 'reset': is chainable
    public function reset() {
        return $this->rewind();
    }

    // API-CHANGE: 'reversed': is not in place (<=> array_reverse)
    public function reversed() {
        return new static(...array_reverse($this->_elements));
    }

    // API-CHANGE: 'reverse': is in place
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
            $this->rewind();
            return $removed_element;
        }
        return null;
    }

    // API-CHANGE: 'shuffle': @throws Exception
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
        $removed_elements = array_splice($this->_elements, $offset, $length, $new_elements);
        $this->_size += -count($removed_elements) + count($new_elements);
        return new static(...$removed_elements);
    }

    // API-CHANGE: 'udiff': not implemented (callback can be passed to 'diff')
    // API-CHANGE: 'uintersect': not implemented (callback can be passed to 'intersect')

    // API-CHANGE: 'unshift': chainable, @return $this instead of $new_length
    public function unshift(...$args) {
        $length = array_unshift($this->_elements, ...$args);
        $this->_size += $length;
        return $this;
    }

    // API-CHANGE: 'walk_recursive': throws Exception
    public function walk_recursive(...$args) {
        if (array_walk_recursive($this->_elements, ...$args)) {
            return $this;
        }
        throw new Exception("Arr::walk_recursive: Some unknow error during recursion.", 1);
    }

    // API-CHANGE: 'walk': throws Exception
    public function walk(...$args) {
        if (array_walk($this->_elements, ...$args)) {
            return $this;
        }
        throw new Exception("Arr::walk_recursive: Some unknow error during recursion.", 1);
    }
    // API-CHANGE: 'without': new. like 'diff' but each argument is an element and not an Arr/array
    public function without(...$args) {
        return $this->diff(new Arr(...$args));
    }

    //////////////////////////////////////////////////////////////////////////////////////////
    // EXTENDING THE API: adapt to python mutable sequence API

    // API-CHANGE: 'append': new function (alias for push)
    public function append(...$args) {
        return $this->push(...$args);
    }

    // clear() is implemented above (collection interface section)

    // API-CHANGE: 'extend': new function
    public function extend($iterable) {
        foreach ($iterable as $key => $value) {
            $new_length = array_push($this->_elements, $value);
        }
        $this->_size = $new_length;
        return $this;
    }

    // API-CHANGE: 'index': new function
    public function index($needle, $start=0, $stop=null, $equality='__equals') {
        if ($stop === null) {
            $stop = $this->_size;
        }
        for ($i = $start; $i < $stop; $i++) {
            if (call_user_func($equality, $this->_elements[$i], $needle) === true) {
                return $i;
            }
        }
        return null;
    }

    public function insert($index, ...$elements) {
        return $this->splice($index, 0, ...$elements);
    }

    // pop([index]) is implemented above (php array section)
    // remove(object) is implemented above (collection interface section)


    ////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED

}

?>
