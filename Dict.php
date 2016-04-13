<?php

require_once 'funcs.php';
require_once 'Arr.php';
require_once 'Map.php';

// NOTE: supports mutable objects as keys but if the key's hash changes Dict does NOT take care of it
class Dict extends Map implements ArrayAccess, Countable, Iterator {

    // maps hashes to lists of values
    protected $_dict;
    // // maps hashes to list of keys (needed for the keys() method)
    // protected $_keys;
    protected $_size;

    function __construct($default_val=null, $arr=[]) {
        $this->_dict = [];
        // $this->_keys = [];
        $this->_size = 0;
        $this->default_val = $default_val;

        foreach ($arr as $key => $value) {
            $this->put($key, $value);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED

    protected function _get_hash($key) {
        try {
            $k = __hash($key);
        } catch (Exception $e) {
            if (is_object($key) && property_exists($key, '__uniqid__')) {
                $k = $key->__uniqid__;
            }
            else {
                $k = uniqid('', true);
            }
        }
        return $k;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING MAP (COLLECTION)

    public function add($key, $value) {
        return $this->put($key, $value);
    }

    public function clear() {
        $this->_dict = [];
        $this->_size = 0;
        return $this;
    }

    public function equals($map) {

    }

    public function has($key) {
        return $this->has_key($key);
    }

    public function hash() {
        // TODO: sum of hashes of keys-value pairs
    }

    public function is_empty() {
        return $this->_size === 0;
    }

    public function remove($key) {

    }

    public function size() {
        return $this->_size;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COUNTABLE

    public function count() {
        return $this->_size;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ARRAYACCESS

    public function offsetExists($offset) {
        return $this->has_key($offset);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        return $this->put($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->remove($offset);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR
    // TODO

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
    // custom methods (for better usability)

    // public function rehash_key($key) {
    //     // rehashing is not necessary for uniqid'ed keys
    //     if (is_object($key) && property_exists($key, '__uniqid__')) {
    //         return $this;
    //     }
    //     $new_hash = $this->_get_hash($key);
    //     // TODO:
    // }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING MAP (remaining function)

    public function copy() {

    }

    public static function fromkeys($iterable=[], $value=null) {
        $res = new static();
        foreach ($iterable as $idx => $key) {
            $res->put($key, $value);
        }
        return $res;
    }

    public function get($key) {
        $k = $this->_get_hash($key);

        if (array_key_exists($k, $this->_dict)) {
            $list = $this->_dict[$k];
            foreach ($list as $idx => $tuple) {
                if (__equals($key, $tuple[0])) {
                    return $tuple[1];
                }
            }
            return $this->default_val;
        }
        return $this->default_val;
    }

    public function has_key($key) {
        $k = $this->_get_hash($key);
        return array_key_exists($k, $_dict);
    }

    public function has_value($value) {
        foreach ($this->_dict as $key => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                if (__equals($value, $tuple[1])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function items() {
        $res = new Arr();
        foreach ($this->_dict as $key => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                $res->push(new Arr(__clone($tuple)));
            }
        }
        return $res;
    }

    public function keys() {
        $res = new Arr();
        foreach ($this->_dict as $key => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                $res->push($tuple[0]);
            }
        }
        return $res;
    }

    public function pop() {

    }

    public function popitem() {

    }

    public function put($key, $value) {
        $k = $this->_get_hash($key);
        // // cache hash
        if (is_object($key) && !property_exists($key, '__uniqid__')) {
            $key->__uniqid__ = $k;
        }

        if (is_float($k)) {
            echo '$k ';
            var_dump($k);
            var_dump($key);
            var_dump($value);
            echo '<br>';

        }

        // add key with new hash (new bucket)
        if (!array_key_exists($k, $this->_dict)) {
            $this->_dict[$k] = [[$key, $value]];
            $this->_size++;
            return $this;
        }
        // else: add key with existing hash
        $list = $this->_dict[$k];
        foreach ($list as $idx => $tuple) {
            if (__equals($key, $tuple[0])) {
                // also update key for reference equality
                $this->_dict[$k][$idx][0] = $key;
                $this->_dict[$k][$idx][1] = $value;
                return $this;
            }
        }
        array_push($this->_dict[$k], [$key, $value]);
        $this->_size++;
        return $this;
    }

    public function setdefault() {

    }

    // update(...)
    //     D.update([E, ]**F) -> None.  Update D from dict/iterable E and F.
    //     If E is present and has a .keys() method, then does:  for k in E: D[k] = E[k]
    //     If E is present and lacks a .keys() method, then does:  for k, v in E: D[k] = v
    //     In either case, this is followed by: for k in F:  D[k] = F[k]
    public function update() {

    }

    public function values() {
        $res = new Arr();
        foreach (array_values($this->_dict) as $key => $list) {
            $res->merge($list);
        }
        return $res->values();
    }

}

class_alias('Dict', 'HashMap');
class_alias('Dict', 'Dictionary');

?>
