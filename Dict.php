<?php

require_once 'funcs.php';
require_once 'Arr.php';
require_once 'MapInterface.php';

class Dict implements Map {

    protected $keys;
    protected $vals;

    function __construct($arr=[]) {
        $this->keys = new Arr();
        $this->vals = new Arr();
        foreach ($arr as $key => $value) {
            # code...
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

    public function add($object) {

    }

    public function clear() {

    }

    public function equals($map) {

    }
    public function has($object) {

    }

    public function hash() {

    }

    public function is_empty() {

    }

    public function remove($key) {

    }

    public function size() {

    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING MAP (remaining function)

    public function copy() {

    }

    public function fromkeys() {

    }

    public function get($key) {

    }

    public function has_key($key) {

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

class_alias('Dict', 'Hash');
class_alias('Dict', 'Dictionary');

?>
