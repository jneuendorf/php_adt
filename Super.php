<?php

require_once 'Clonable.php';
require_once 'Hashable.php';

abstract class Super extends Clonable implements Countable, Hashable {

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COUNTABLE
    public function count() {
        return $this->size();
    }
    
    abstract public function size();
}


?>
