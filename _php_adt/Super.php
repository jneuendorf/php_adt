<?php
/**
* @package _php_adt
*/
namespace _php_adt;
use _php_adt\Clonable as Clonable;
use _php_adt\Hashable as Hashable;

require_once 'Clonable.php';
require_once 'Hashable.php';

abstract class Super extends Clonable implements \Countable, Hashable {

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COUNTABLE
    public function count() {
        return $this->size();
    }

    abstract public function size();
}
