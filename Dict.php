<?php

require_once 'funcs.php';
require_once 'Arr.php';
require_once 'Map.php';

class Dict extends Map {

    protected $_dict;
    protected $_size;

    function __construct($default_val=null, $arr=[]) {
        $this->_dict = [];
        $this->_size = 0;
        $this->default_val = $default_val;

        foreach ($arr as $key => $value) {
            $this->put($key, $value);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED

    protected function _get_key_idx($key) {
        foreach ($this->keys as $idx => $k) {
            if (equals($k, $key)) {
                return $idx;
            }
        }
        return -1;
    }

    protected function _get_hash($key) {
        try {
            $k = __hash($key);
        } catch (Exception $e) {
            $k = uniqid('', true);
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
    // IMPLEMENTING MAP (remaining function)

    public function copy() {

    }

    public function fromkeys() {

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

    }

    public function items() {

    }

    public function keys() {

    }

    public function pop() {

    }

    public function popitem() {

    }

    public function put($key, $value) {
        $k = $this->_get_hash($key);
        // cache hash
        if (is_object($key) && !property_exists($key, '__hash__')) {
            $key->__hash__ = $k;
        }

        // add key with new hash
        if (!array_key_exists($k, $this->_dict)) {
            $this->_dict[$k] = [[$key, $value]];
            return $this;
        }
        // else: add key with existing hash
        $list = $this->_dict[$k];
        foreach ($list as $idx => $tuple) {
            if (__equals($key, $tuple[0])) {
                // also update key for reference equality
                $this->_dict[$k][$idx][0] = $key;
                $this->_dict[$k][$idx][1] = $value;
                // $tuple[0] = $key;
                // $tuple[1] = $value;
                return $this;
            }
        }
        array_push($this->_dict[$k], [$key, $value]);
        return $this;
    }

    public function setdefault() {

    }

    public function update() {

    }

    public function values() {

    }

}

class_alias('Dict', 'HashMap');
class_alias('Dict', 'Dictionary');

?>
