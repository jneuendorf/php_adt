<?php

interface Collection {
    public function add($object);
    public function clear();
    public function equals($collection);
    public function has($object);
    public function hash();
    public function is_empty();
    public function remove($object);
    public function size();
}

?>
