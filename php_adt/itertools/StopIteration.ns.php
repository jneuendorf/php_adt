<?php

namespace php_adt\itertools;
use \Exception as Exception;

class StopIteration extends Exception {
    /**
    * the return value of the iterator that caused the exception.
    * @var mixed
    */
    public $result;

    public function __construct($result = null, Exception $previous = null) {
        $this->result = $result;
        parent::__construct("StopIteration: $result", 0, $previous);
    }

    public function __toString() {
        return __CLASS__.": [{$this->code}]: {$this->result}\n";
    }
}
