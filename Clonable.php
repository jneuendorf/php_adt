<?php

abstract class Clonable {
    public function __clone() {
        return $this->copy(false);
    }

    abstract public function copy($deep=false);
}

?>
