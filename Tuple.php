<?php

require_once 'init.php';

class Tuple {

    protected $first;
    protected $second;

    public function __construct($first, $second) {
        $this->first = $first;
        $this->second = $second;
    }

    public function to_a() {
        return new Arr($this->first, $this->second);
    }

    public function __toString() {
        return "(".__toString($this->first).", ".__toString($this->second).")";
    }

    // TODO API

}

?>
