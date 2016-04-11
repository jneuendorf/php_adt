<?php

require_once 'funcs.php';
require_once 'Arr.php';
require_once 'MapInterface.php';

class Dict implements Map {

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

    ////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING MAP (COLLECTION)

    public function add($key, $value) {

    }

    public function clear() {
        $this->_dict = [];
        $this->_size = 0;
        return $this;
    }

    public function equals($map) {

    }

    public function has($key) {

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
        try {
            $k = __hash($key);
        } catch (Exception $e) {
            $k = uniqid('', true);
        }

        if (array_key_exists($k, $_dict)) {
            $list = $this->_dict[$k];
            if (count($list) === 1) {
                return $list[0][1];
            }
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
        if (method_exists($key, "hash")) {
            $k = $key->hash();
        }
        else {
            $k = uniqid('', true);
        }
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
