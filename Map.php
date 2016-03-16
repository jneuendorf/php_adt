<?php

require_once 'Collection.php';

interface Map extends Collection {
    public function equals($map);
    public function get($key);
    public function has_key($key);
    public function has_value($value);
    public function put($key, $value);
    public function remove($key);
    public function values();
}

?>
