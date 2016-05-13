<?php
/**
* @package _php_adt
*/
namespace _php_adt;

require_once 'Super.php';

/**
* Interface for Tree.
*/
abstract class AbstractTree extends Super {
    abstract public function add($tree_node, $index=null);
    abstract public function children();
    abstract public function clear();
    abstract public function depth();
    abstract public function descendants();
    // abstract public function equals($tree);
    abstract public function find($filter);
    abstract public function is_leaf();
    abstract public function leaves();
    abstract public function level_siblings();
    abstract public function level();
    abstract public function parent();
    abstract public function remove();
    abstract public function root();
    abstract public function siblings();
}
