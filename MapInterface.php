<?php

require_once 'CollectionInterface.php';

interface Map extends Collection {
    // from collection
    // public function add($object);
    // public function clear();
    // public function equals($collection);
    // public function has($object);
    // public function hash();
    // public function is_empty();
    // public function remove($object);
    // public function size();
    // from java
    // public function equals($map);
    public function get($key);
    public function has_key($key);
    public function has_value($value);
    public function put($key, $value);
    public function remove($key);
    public function values();
    // from python
    // clear
    public function copy();
    public function fromkeys();
    // get
    public function items();
    public function keys();
    public function pop();
    public function popitem();
    public function setdefault();
    public function update();
    // public function values();


}

?>
