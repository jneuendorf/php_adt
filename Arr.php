<?php

require_once 'init.php';
require_once 'AbstractCollection.php';
require_once 'Dict.php';

/**
 * Arr is a wrapper around the native array type.
 * It provides a consistent API and some extra features. There are no keys...just plain indices. ^^
 * @property int length Equivalent for calling the size() method.
 */
class Arr extends AbstractCollection implements ArrayAccess, Iterator {

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
        'array_values',
    ];

    /**
     * @var int $_size Internally used size of the Array.
    */
    protected $_size = 0;
    /**
    * @var array $_elements Internal array of elements.
    */
    protected $_elements = [];
    /**
     * @var int $_position Internal pointer to the current element.
    */
    protected $_position;

    /**
     * @param mixed... $elements
    */
    public function __construct(...$elements) {
        foreach ($elements as $idx => $element) {
            array_push($this->_elements, $element);
        }
        $this->_size = count($this->_elements);
    }

    // STATIC

    /**
     * @param Traversable $iterable
     * @param bool $recursive
     * @return Arr
     */
    public static function from_iterable($iterable, $recursive=true) {
        $result = new Arr();
        foreach ($iterable as $key => $value) {
            if ($recursive && (is_array($value) || ($value instanceof Traversable))) {
                $value = Arr::from_iterable($value, $recursive);
            }
            $result->push($value);
        }
        return $result;
    }

    /**
     * @param int $count
     * @param mixed $value
     * @return Arr
     */
    public static function fill($count, $value=null) {
        return new static(...array_fill(0, $count, $value));
    }

    /**
     * @param mixed $start
     * @param mixed $end
     * @param number $step
     * @return Arr
     */
    public static function range($start, $end, $step=1) {
        return new static(...range($start, $end, $step));
    }

    ////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////
    // INSTANCE

    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED

    protected function _convenience_get($index, $args) {
        // no default value => try at index
        if (count($args) === 0) {
            return $this->_elements[$this->_adjust_offset($index)];
        }
        // use default value
        return $args[0];
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC

    public function __get($name) {
        if ($name === 'length') {
            return $this->_size;
        }
        throw new Exception("Cannot get '$name' of instance of Arr!", 1);
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

    /**
     * Stringyfies the Arr instance.
     * @return string
     */
    public function __toString() {
        return __toString($this->_elements);
    }

    /**
     * Reveals the internal array of elements. This should be used rarely.
     * @return array
     */
    public function elements() {
        return $this->_elements;
    }

    /**
     * Converts the Arr instance to a native array.
     * @return array
     */
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

    /**
     * Creates a copy of the Arr instance.
     * @return Arr
     */
    public function to_arr() {
        return $this->copy();
    }

    /**
     * Converts the Arr instance to an instance of Dict (indices become the keys).
     * @return Arr
     */
    public function to_dict() {
        return new Dict(null, $this);
    }

    /**
    * Converts the Arr instance to an instance of Set.
    * @return Arr
    */
    public function to_set() {
        return new Set(...$this->_elements);
    }

    /**
    * Returns the first element of the Arr instance.
    * @param mixed $default_val An optional argument that (if given) is returned if there is no first element. If not given an exception may be thrown.
    * @throws Exception
    * @return mixed
    */
    public function first($default_val=null) {
        return $this->_convenience_get(0, func_get_args());
    }

    /**
    * Returns the second element of the Arr instance.
    * @param mixed $default_val An optional argument that (if given) is returned if there is no second element. If not given an exception may be thrown.
    * @throws Exception
    * @return mixed
    */
    public function second($default_val=null) {
        return $this->_convenience_get(1, func_get_args());
    }

    /**
    * Returns the third element of the Arr instance.
    * @param mixed $default_val An optional argument that (if given) is returned if there is no third element. If not given an exception may be thrown.
    * @throws Exception
    * @return mixed
    */
    public function third($default_val=null) {
        return $this->_convenience_get(2, func_get_args());
    }

    /**
    * Returns the penultimate element of the Arr instance.
    * @param mixed $default_val An optional argument that (if given) is returned if there is no penultimate element. If not given an exception may be thrown.
    * @throws Exception
    * @return mixed
    */
    public function penultimate($default_val=null) {
        return $this->_convenience_get(-2, func_get_args());
    }

    /**
    * Returns the last element of the Arr instance.
    * @param mixed $default_val An optional argument that (if given) is returned if there is no last element. If not given an exception may be thrown.
    * @throws Exception
    * @return mixed
    */
    public function last($default_val=null) {
        return $this->_convenience_get(-1, func_get_args());
    }


    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COLLECTION

    /**
    * Adds one or more elements to the Arr instance. <span class="label label-info">Chainable</span>
    * @param mixed... $elements The elements to be added.
    * @return Arr
    */
    public function add(...$elements) {
        foreach ($elements as $idx => $element) {
            $this->push($element);
        }
        return $this;
    }

    /**
    * Creates a (potentially deep) copy of the Arr instance.
    * @param bool $deep Whether to copy recursively.
    * @return Arr
    */
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

    /**
    * Removes all elements from the Arr instance. <span class="label label-info">Chainable</span>
    * @return Arr
    */
    public function clear() {
        $this->_elements = [];
        $this->_size = 0;
        return $this;
    }

    /**
    * Indicates whether the Arr instance is equals to another object.
    * @param mixed $arr
    * @return bool
    */
    public function equals($arr) {
        if (is_object($arr) && $arr instanceof self) {
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

    /**
    * Indicates whether an object is containted in the Arr instance.
    * @param mixed $object
    * @param Callable $equality This optional parameter can be used to define how objects are considered equal.
    * @return bool
    */
    public function has($object, $equality='__equals') {
        return $this->index($object, 0, $this->_size, $equality) !== null;
    }

    /**
    * Calculates a hash value of the Arr instance.
    * @return int
    */
    public function hash() {
        $result = 0;
        foreach ($this->_elements as $idx => $element) {
            $result += ($idx + 1) * __hash($element);
        }
        return $result;
    }

    /**
    * Removes an object from the Arr instance if it exists.
    * @param mixed $object
    * @param Callable $equality This optional parameter can be used to define how objects are considered equal.
    * @return bool
    */
    public function remove($object, $equality='__equals') {
        $index = $this->index($object, 0, $this->_size, $equality);
        if ($index !== null) {
            $this->splice($index, 1);
        }
        return $this;
    }

    /**
    * Removes an object from the Arr instance at a given index.
    * @param int $index
    * @return Arr An Arr instance containing the removed element.
    */
    public function remove_at($index) {
        return $this->splice($index, 1);
    }

    /**
    * Returns the current size of the Arr instance.
    * @return int
    */
    public function size() {
        return $this->_size;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ARRAYACCESS

    /**
     * @internal
    */
    protected function _adjust_offset($offset) {
        if ($this->offsetExists($offset)) {
            if ($offset < 0) {
                $offset += $this->_size;
            }
            return $offset;
        }
        throw new Exception("Undefined offset $offset!", 1);
    }

    /**
     * @internal
    */
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
            if (preg_match('/^\-?\d+\:(\-?\d+)?$/', $offset) === 1) {
                $use_slicing = true;
                $parts = explode(':', $offset);
                $start = (int) $parts[0];
                if (strlen($parts[1]) > 0) {
                    $end = (int) $parts[1];
                }
                else {
                    $end = null;
                }
            }
            else {
                throw new Exception('Invalid string offset \''.$offset.'\'. String offsets must have the form \'int1:(int2)\'.');
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
        try {
            $end = $this->_adjust_offset($end);
        } catch (Exception $e) {
            $end = $this->size();
        }

        return [
            'start' => $this->_adjust_offset($start),
            'end' => $end,
            'slicing' => $use_slicing
        ];
    }

    /**
     * @internal
    */
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

    /**
     * @internal
    */
    public function offsetGet($offset) {
        $bounds = $this->_get_start_end_from_offset($offset);
        if (!$bounds['slicing']) {
            return $this->_elements[$bounds['start']];
        }
        return $this->slice($bounds['start'], $bounds['end'] - $bounds['start']);
    }

    /**
     * @internal
    */
    public function offsetSet($offset, $value) {
        // TODO enable slicing notation for setting subarrays
        // called like $my_arr[] = 2; => push
        if ($offset === null) {
            $this->push($value);
        }
        else {
            $this->_elements[$this->_adjust_offset($offset)] = $value;
        }
        return $this;
    }

    /**
     * @internal
    */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->_elements[$offset]);
            $this->_size--;
            // reassign keys
            $this->_elements = array_values($this->_elements);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    /**
    * Returns the current element.
    * @return mixed
    */
    public function current() {
        return $this->_elements[$this->_position];
    }

    /**
    * Returns the index of the current element.
    * @return int
    */
    public function key() {
        return $this->_position;
    }

    /**
    * Moves the cursor to the next element (the one after the current element).
    */
    public function next() {
        $this->_position++;
    }

    /**
    * Moves the cursor to the first element.
    */
    public function rewind() {
        $this->_position = 0;
    }

    /**
    * @internal
    */
    public function valid() {
        return $this->_position >= 0 && $this->_position < $this->_size;
    }


    ////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////
    // DELEGATIONS TO NATIVE METHODS

    ////////////////////////////////////////////////////////////////////////////////////
    // INSTANCE
    // implementations based on the API of native methods

    // API-CHANGE: 'change_key_case' function not implemented
    // API-CHANGE: new function 'concat'
    /**
    * Concatenates the Arr instance with one or more Arr instances.
    * @param mixed... $arrs The Arr instance(s) to be concatenated.
    * @return Arr
    */
    public function concat(...$arrs) {
        $res = new Arr(...$this->_elements);
        foreach ($arrs as $idx => $arr) {
            $res->merge(...$arrs);
        }
        return $res;
    }

    // API-CHANGE: 'count_values': returns Dict
    /**
    * Counts how often each element occurs in the Arr instance and returns a dictionary in which each an elements maps to the number of its occurences.
    * @param Callable $group_func Optionally, a function can be passed to define how the dictionary is built. See 'group_by()' for more details.
    * @return Dict
    */
    public function count_values($group_func=null) {
        $res = new Dict();
        foreach ($this->group_by($group_func) as $key => $value) {
            $res->put($key, $value->size());
        }
        return $res;
    }

    /**
    * Calculates the difference between this Arr instance and the given one. The resulting Arr instance contains all the old elements except the ones also contained in the given instance.
    * @param Arr $arr The Arr instance whose elements are excepted.
    * @param Callable $equality This optional parameter can be used to define how objects are considered equal.
    * @return Arr
    */
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
    /**
    * Synonym for 'diff()'.
    */
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

    public function popfirst() {
        return $this->pop(0);
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
        $offset = $this->_adjust_offset($offset);
        $removed_elements = array_splice($this->_elements, $offset, $length, $new_elements);
        $this->_size += -count($removed_elements) + count($new_elements);
        return new static(...$removed_elements);
    }

    public function unique($equality='__equals') {
        $res = new Arr();
        foreach ($this as $idx => $element) {
            if ($idx === 0 || !$res->has($element, $equality)) {
                $res->push($element);
            }
        }
        return $res;
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


}

?>
