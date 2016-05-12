<?php
/**
* @package _php_adt
*/
namespace _php_adt;

require_once 'AbstractCollection.php';

/**
* Interface for Dict.
*/
abstract class AbstractMap extends AbstractCollection {
    // from collection
    public function add(...$keys) {}
    // public function clear() {}
    // public function equals($collection) {}
    public function has($key) {}
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
    // from python
    // clear
    public static function fromkeys() {}
    // get
    public function items() {}
    public function keys() {}
    public function pop($key, $default=null) {}
    public function popitem() {}
    public function setdefault() {}
    public function update($iterable) {}
    // public function values() {}
}
