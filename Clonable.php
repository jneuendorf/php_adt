<?php

namespace _php_adt;

abstract class Clonable {
    /**
    * Make subclasses clonable using the 'clone' keyword.
    * This is the same as calling the 'copy()' method with $deep set to true.
    */
    public function __clone() {
        return $this->copy(true);
    }

    abstract public function copy($deep=false);
}
