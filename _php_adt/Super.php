<?php
/**
* @package _php_adt
*/
namespace _php_adt;

require_once 'Clonable.php';
require_once 'Comparable.php';
require_once 'Hashable.php';


abstract class Super extends \_php_adt\Clonable implements \_php_adt\Comparable, \Countable, \_php_adt\Hashable {

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COUNTABLE
    public function count() {
        return $this->size();
    }

    public function is_empty() {
        return $this->size() === 0;
    }

    abstract public function size();

    public function __eq__($object) {
        return $this->equals($object);
    }

    public function __len__() {
        return $this->size();
    }

}
