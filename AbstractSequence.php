<?php

require_once 'AbstractCollection.php';

abstract class AbstractSequence extends AbstractCollection implements ArrayAccess, Countable, Iterator {
    // from collection
    // public function add($object) {}
    // public function clear() {}
    // public function equals($collection) {}
    // public function has($object) {}
    // public function hash() {}
    // public function is_empty() {}
    // public function remove($object) {}
    // public function size() {}
    // from java
    // public function equals($map) {}
    public function get($key, $default_val=null) {}
    public function has_key($key) {}
    public function has_value($value) {}
    public function put($key, $value) {}
    public function remove($key) {}
    public function values() {}



}

?>
