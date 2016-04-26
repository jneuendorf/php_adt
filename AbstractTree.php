<?php

require_once 'Hashable.php';

abstract class AbstractTree implements Hashable {

    public function add($tree_node, $index=null) {}
    public function children() {}
    public function depth() {}
    public function descendants() {}
    public function find($filter) {}
    public function has($tree_node) {}
    public function is_leaf() {}
    public function leaves() {}
    public function level_siblings() {}
    public function level() {}
    public function parent() {}
    public function remove() {}
    public function root() {}
    public function siblings() {}
    public function size() {}

}


?>
