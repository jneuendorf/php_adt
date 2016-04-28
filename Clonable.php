<?php

abstract class Clonable {
    public function __clone() {
        return $this->copy(true);
    }

    abstract public function copy($deep=false);
}

?>
