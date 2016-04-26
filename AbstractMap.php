<?php

require_once 'AbstractCollection.php';

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


    protected function _get_hash($object) {
        try {
            $hash = __hash($object);
        }
        catch (Exception $e) {
            if (is_object($object) && property_exists($object, '__uniqid__')) {
                $hash = $object->__uniqid__;
            }
            else {
                $hash = uniqid('', true);
            }
        }
        return $hash;
    }


}

?>
